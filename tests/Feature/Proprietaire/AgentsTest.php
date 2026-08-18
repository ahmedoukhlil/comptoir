<?php

namespace Tests\Feature\Proprietaire;

use App\Livewire\Proprietaire\Agents;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AgentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_proprietaire_peut_creer_un_agent_sur_son_point(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'reseau', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($proprietaire);

        Livewire::test(Agents::class)
            ->set('pointId', $point->id)
            ->set('nom', 'Nouvel Agent')
            ->set('telephone', '77112233')
            ->set('role', 'agent')
            ->call('creer')
            ->assertSet('motDePasseGenere', fn ($v) => is_string($v) && strlen($v) === 10);

        $agent = User::where('telephone', '77112233')->first();
        $this->assertNotNull($agent);
        $this->assertEquals($tenant->id, $agent->tenant_id);
        $this->assertEquals($point->id, $agent->point_id);
        $this->assertTrue($agent->estAgent());
    }

    public function test_proprietaire_solo_peut_aussi_creer_un_agent(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($proprietaire);

        $this->get('/tableau-de-bord/agents')->assertOk();

        Livewire::test(Agents::class)
            ->set('pointId', $point->id)
            ->set('nom', 'Agent Solo')
            ->set('telephone', '88223344')
            ->call('creer')
            ->assertSet('motDePasseGenere', fn ($v) => is_string($v));

        $this->assertNotNull(User::where('telephone', '88223344')->first());
    }

    public function test_proprietaire_ne_peut_pas_creer_un_agent_sur_le_point_dun_autre_tenant(): void
    {
        $tenantA = Tenant::create(['nom' => 'A', 'plan' => 'reseau', 'statut' => 'actif']);
        $tenantB = Tenant::create(['nom' => 'B', 'plan' => 'reseau', 'statut' => 'actif']);
        $pointB = Point::create(['tenant_id' => $tenantB->id, 'nom' => 'Point B']);
        $proprietaireA = User::factory()->proprietaire()->create(['tenant_id' => $tenantA->id]);

        $this->actingAs($proprietaireA);

        Livewire::test(Agents::class)
            ->set('pointId', $pointB->id)
            ->set('nom', 'Intrus')
            ->set('telephone', '99887766')
            ->call('creer')
            ->assertHasErrors('pointId');

        $this->assertNull(User::where('telephone', '99887766')->first());
    }

    public function test_agent_ne_peut_pas_acceder_a_lecran_de_gestion_des_agents(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($agent)->get('/tableau-de-bord/agents')->assertForbidden();
    }

    public function test_creation_bloquee_en_lecture_seule(): void
    {
        $tenant = Tenant::create([
            'nom' => 'T',
            'plan' => 'solo',
            'statut' => 'essai',
            'essai_expire_le' => Carbon::now()->subDay(),
        ]);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($proprietaire);

        Livewire::test(Agents::class)
            ->set('pointId', $point->id)
            ->set('nom', 'Bloque')
            ->set('telephone', '11223344')
            ->call('creer')
            ->assertHasErrors('lectureSeule');

        $this->assertNull(User::where('telephone', '11223344')->first());
    }
}

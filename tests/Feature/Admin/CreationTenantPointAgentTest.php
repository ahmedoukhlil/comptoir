<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Agents\Create as AgentsCreate;
use App\Livewire\Admin\Points\Create as PointsCreate;
use App\Livewire\Admin\Tenants\Create as TenantsCreate;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreationTenantPointAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_peut_creer_un_tenant_avec_son_premier_point_et_proprietaire(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);

        Livewire::test(TenantsCreate::class)
            ->set('tenantNom', 'Nouveau Client SARL')
            ->set('plan', 'reseau')
            ->set('pointNom', 'Kiosque Ksar')
            ->set('pointLocalisation', 'Nouakchott, Ksar')
            ->set('proprietaireNom', 'Ahmed Salem')
            ->set('proprietaireTelephone', '44556677')
            ->call('creer')
            ->assertSet('motDePasseGenere', fn ($v) => is_string($v) && strlen($v) === 10);

        $tenant = Tenant::where('nom', 'Nouveau Client SARL')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('reseau', $tenant->plan);
        $this->assertEquals('actif', $tenant->statut);

        $point = $tenant->points()->first();
        $this->assertEquals('Kiosque Ksar', $point->nom);

        $proprietaire = User::where('telephone', '44556677')->first();
        $this->assertTrue($proprietaire->estProprietaire());
        $this->assertEquals($point->id, $proprietaire->point_id);
    }

    public function test_super_admin_peut_ajouter_un_point_a_un_tenant_existant(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tenant = Tenant::create(['nom' => 'Alpha', 'plan' => 'reseau', 'statut' => 'actif']);

        $this->actingAs($admin);

        Livewire::test(PointsCreate::class, ['tenantId' => $tenant->id])
            ->set('nom', 'Kiosque Sebkha')
            ->set('localisation', 'Nouakchott, Sebkha')
            ->call('creer')
            ->assertSet('cree', true);

        $this->assertCount(1, $tenant->points()->get());
        $this->assertEquals('Kiosque Sebkha', $tenant->points()->first()->nom);
    }

    public function test_super_admin_peut_ajouter_un_agent_a_un_point_existant(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tenant = Tenant::create(['nom' => 'Alpha', 'plan' => 'reseau', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);

        $this->actingAs($admin);

        Livewire::test(AgentsCreate::class, ['pointId' => $point->id])
            ->set('nom', 'Mariem Ba')
            ->set('telephone', '55667788')
            ->set('role', 'agent')
            ->call('creer')
            ->assertSet('motDePasseGenere', fn ($v) => is_string($v) && strlen($v) === 10);

        $agent = User::where('telephone', '55667788')->first();
        $this->assertNotNull($agent);
        $this->assertTrue($agent->estAgent());
        $this->assertEquals($point->id, $agent->point_id);
        $this->assertEquals($tenant->id, $agent->tenant_id);
    }

    public function test_agent_cree_peut_se_connecter_avec_le_mot_de_passe_genere(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tenant = Tenant::create(['nom' => 'Alpha', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);

        $this->actingAs($admin);

        $component = Livewire::test(AgentsCreate::class, ['pointId' => $point->id])
            ->set('nom', 'Test Agent')
            ->set('telephone', '66778899')
            ->call('creer');

        $motDePasse = $component->get('motDePasseGenere');

        $this->assertTrue(\Illuminate\Support\Facades\Auth::attempt([
            'telephone' => '66778899',
            'password' => $motDePasse,
        ]));
    }
}

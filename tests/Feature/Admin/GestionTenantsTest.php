<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tenants\Show;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionTenantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_voit_la_liste_des_tenants(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Tenant::create(['nom' => 'Alpha', 'plan' => 'solo', 'statut' => 'actif']);
        Tenant::create(['nom' => 'Beta', 'plan' => 'reseau', 'statut' => 'actif']);

        $this->actingAs($admin)
            ->get('/admin/tenants')
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Beta');
    }

    public function test_agent_et_proprietaire_ne_peuvent_pas_acceder_au_back_office(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($agent)->get('/admin/tenants')->assertForbidden();
        $this->actingAs($proprietaire)->get('/admin/tenants')->assertForbidden();
    }

    public function test_super_admin_peut_changer_le_plan_et_le_statut_dun_tenant(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tenant = Tenant::create(['nom' => 'Alpha', 'plan' => 'solo', 'statut' => 'essai']);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['tenantId' => $tenant->id])
            ->call('changerPlan', 'reseau')
            ->call('changerStatut', 'actif');

        $tenant->refresh();
        $this->assertEquals('reseau', $tenant->plan);
        $this->assertEquals('actif', $tenant->statut);
    }

    public function test_changement_de_plan_debloque_immediatement_le_dashboard_du_proprietaire(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $tenant = Tenant::create(['nom' => 'Alpha', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($admin);
        Livewire::test(Show::class, ['tenantId' => $tenant->id])->call('changerPlan', 'reseau');

        $this->actingAs($proprietaire->fresh())
            ->get('/tableau-de-bord')
            ->assertOk();
    }
}

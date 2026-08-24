<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Recorre todas las pantallas del CRM y del portal para detectar errores de
 * renderizado que no aparecen hasta que se abre cada página.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(): User
    {
        return User::where('role', UserRole::Admin)->firstOrFail();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_render(string $path): void
    {
        $this->actingAs($this->admin())
            ->get($path)
            ->assertSuccessful();
    }

    public static function adminPages(): array
    {
        return [
            'escritorio' => ['/admin'],
            'embudo' => ['/admin/pipeline'],
            'ajustes del portal' => ['/admin/portal-settings'],
            'leads' => ['/admin/leads'],
            'nuevo lead' => ['/admin/leads/create'],
            'contactos' => ['/admin/contacts'],
            'nuevo contacto' => ['/admin/contacts/create'],
            'visitas' => ['/admin/visits'],
            'nueva visita' => ['/admin/visits/create'],
            'contratos' => ['/admin/contracts'],
            'nuevo contrato' => ['/admin/contracts/create'],
            'comisiones' => ['/admin/commissions'],
            'propiedades' => ['/admin/properties'],
            'nueva propiedad' => ['/admin/properties/create'],
            'amenidades' => ['/admin/amenities'],
            'usuarios' => ['/admin/users'],
            'nuevo usuario' => ['/admin/users/create'],
        ];
    }

    public function test_admin_record_pages_render(): void
    {
        $admin = $this->admin();

        $paths = [
            '/admin/properties/'.Property::first()->slug,
            '/admin/properties/'.Property::first()->slug.'/edit',
            '/admin/contacts/'.Contact::first()->id,
            '/admin/contacts/'.Contact::first()->id.'/edit',
            '/admin/leads/'.Lead::first()->id,
            '/admin/leads/'.Lead::first()->id.'/edit',
            '/admin/visits/'.Visit::first()->id.'/edit',
            '/admin/contracts/'.Contract::first()->id,
            '/admin/contracts/'.Contract::first()->id.'/edit',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertSuccessful();
        }
    }

    public function test_portal_pages_render(): void
    {
        $property = Property::published()->firstOrFail();

        $this->get('/')->assertSuccessful();
        $this->get('/propiedades')->assertSuccessful();
        $this->get('/propiedades?operation=venta&type=casa&bedrooms=3&sort=price_asc')->assertSuccessful();
        $this->get('/propiedades/'.$property->slug)->assertSuccessful();
    }

    public function test_unpublished_property_is_hidden_from_the_portal(): void
    {
        $hidden = Property::where('published', false)->firstOrFail();

        $this->get('/propiedades/'.$hidden->slug)->assertNotFound();
    }

    public function test_agents_only_see_their_own_records(): void
    {
        $agent = User::where('role', UserRole::Agente)->firstOrFail();
        $otherLead = Lead::where('assigned_agent_id', '!=', $agent->id)->firstOrFail();

        $this->actingAs($agent)
            ->get('/admin/leads/'.$otherLead->id)
            ->assertNotFound();
    }
}

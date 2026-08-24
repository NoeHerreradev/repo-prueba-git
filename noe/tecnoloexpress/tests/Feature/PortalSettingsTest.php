<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\PortalSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(): User
    {
        return User::where('role', UserRole::Admin)->firstOrFail();
    }

    public function test_only_the_administrator_reaches_the_settings_module(): void
    {
        $this->actingAs($this->admin())->get('/admin/portal-settings')->assertSuccessful();

        foreach ([UserRole::Gerente, UserRole::Agente] as $role) {
            // Sesión limpia por rol: si no, `AuthenticateSession` desloguea al
            // segundo usuario al ver que el hash guardado ya no es el suyo.
            $this->flushSession();

            $user = User::where('role', $role)->firstOrFail();

            $this->actingAs($user)
                ->get('/admin/portal-settings')
                ->assertForbidden();

            $this->assertFalse(PortalSettings::canAccess(), "El rol {$role->value} no debería acceder a Ajustes.");
        }
    }

    public function test_typed_accessors_read_each_kind_of_setting(): void
    {
        Setting::set('show_featured', false);
        Setting::set('featured_count', 9);
        Setting::set('benefits', ['Uno', 'Dos']);
        Setting::set('company_name', 'Inmobiliaria Andes');

        $this->assertFalse(Setting::bool('show_featured'));
        $this->assertSame(9, Setting::int('featured_count'));
        $this->assertSame(['Uno', 'Dos'], Setting::list('benefits'));
        $this->assertSame('Inmobiliaria Andes', Setting::get('company_name'));
    }

    public function test_an_empty_value_falls_back_to_the_factory_default(): void
    {
        Setting::set('company_name', '');

        $this->assertSame(Setting::DEFAULTS['company_name'], Setting::get('company_name'));
    }

    public function test_the_brand_color_reaches_the_portal_as_a_css_variable(): void
    {
        Setting::set('brand_color', '#dc2626');

        $this->get('/')
            ->assertSuccessful()
            ->assertSee('--brand: #dc2626', escape: false);
    }

    public function test_an_invalid_brand_color_falls_back_instead_of_breaking_the_page(): void
    {
        Setting::set('brand_color', 'rojo intenso');

        $this->assertSame(Brand::FALLBACK, Brand::color());
        $this->get('/')->assertSuccessful();
    }

    public function test_the_brand_contrast_flips_with_the_background(): void
    {
        Setting::set('brand_color', '#0f172a');
        $this->assertSame('#ffffff', Brand::contrast());

        Setting::set('brand_color', '#fde047');
        $this->assertSame('#111827', Brand::contrast());
    }

    public function test_turning_a_section_off_removes_it_from_the_portal(): void
    {
        Setting::set('featured_heading', 'Lo mejor de la semana');
        $this->get('/')->assertSee('Lo mejor de la semana');

        Setting::set('show_featured', false);
        $this->get('/')->assertDontSee('Lo mejor de la semana');
    }

    public function test_the_featured_count_limits_how_many_properties_are_shown(): void
    {
        Setting::set('featured_count', 3);

        $this->get('/')->assertViewHas('featured', fn ($featured) => $featured->count() === 3);
    }

    public function test_the_texts_configured_are_the_ones_rendered(): void
    {
        Setting::set('hero_title', 'Tu próxima casa te espera');
        Setting::set('contact_heading', 'Hablemos');
        Setting::set('benefits', ['Tasación gratuita']);

        $this->get('/')
            ->assertSee('Tu próxima casa te espera')
            ->assertSee('Hablemos')
            ->assertSee('Tasación gratuita');
    }

    public function test_the_whatsapp_button_appears_only_when_it_is_enabled(): void
    {
        Setting::set('company_whatsapp', '+593 99 123 4567');
        Setting::set('whatsapp_float', true);

        // El número viaja sin espacios ni signos en el enlace.
        $this->get('/')->assertSee('https://wa.me/593991234567', escape: false);

        Setting::set('whatsapp_float', false);
        $this->get('/')->assertDontSee('https://wa.me/593991234567', escape: false);
    }

    public function test_analytics_loads_only_when_an_id_is_configured(): void
    {
        $this->get('/')->assertDontSee('googletagmanager.com', escape: false);

        Setting::set('analytics_id', 'G-TEST12345');

        $this->get('/')->assertSee('G-TEST12345', escape: false);
    }

    public function test_maintenance_mode_closes_the_portal_to_visitors(): void
    {
        Setting::set('portal_enabled', false);
        Setting::set('maintenance_message', 'Volvemos enseguida.');

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Volvemos enseguida.');

        $this->get('/propiedades')->assertStatus(503);
    }

    public function test_the_administrator_still_previews_the_portal_in_maintenance(): void
    {
        Setting::set('portal_enabled', false);

        $this->actingAs($this->admin())
            ->get('/')
            ->assertSuccessful()
            ->assertSee('Solo tú lo ves porque eres administrador');
    }

    public function test_saving_from_the_panel_persists_only_known_keys(): void
    {
        Setting::set('clave_desconocida', 'x');
        $this->assertDatabaseHas('settings', ['key' => 'clave_desconocida']);

        // La página solo escribe claves declaradas en DEFAULTS.
        $this->assertArrayNotHasKey('clave_desconocida', Setting::DEFAULTS);
    }
}

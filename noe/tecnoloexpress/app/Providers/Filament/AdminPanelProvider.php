<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CommissionsByAgentChart;
use App\Filament\Widgets\CrmStatsOverview;
use App\Filament\Widgets\LeadsBySourceChart;
use App\Filament\Widgets\LeadsFunnelChart;
use App\Filament\Widgets\PropertiesByStatusChart;
use App\Filament\Widgets\UpcomingVisitsTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName('Tecnolo CRM')
            ->colors([
                'primary' => Color::Indigo,
            ])
            // Sin icono en el grupo: Filament exige que el icono esté en el grupo
            // o en sus ítems, y los iconos por recurso son más informativos.
            ->navigationGroups([
                NavigationGroup::make('Comercial'),
                NavigationGroup::make('Inventario'),
                NavigationGroup::make('Administración'),
                NavigationGroup::make('Ajustes'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                CrmStatsOverview::class,
                LeadsFunnelChart::class,
                LeadsBySourceChart::class,
                PropertiesByStatusChart::class,
                CommissionsByAgentChart::class,
                UpcomingVisitsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

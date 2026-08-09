<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // [THECHNOLOGY-MOD] : Ganti brand name panel admin + logo kecil di sebelah teks
            // [THECHNOLOGY-FIX] : brandLogo() menggantikan teks — gunakan HtmlString di brandName() agar logo + teks tampil bersamaan
            // [THECHNOLOGY-MOD] : Split brand text jadi 2 baris — "PANEL ADMIN" / "SMAN 1 NGRAHO"
            ->brandName(new HtmlString(
                '<div style="display: flex; align-items: center; gap: 0.75rem;">' .
                '<img src="' . asset('images/branding/logo-panel-admin.webp') . '" alt="Logo" style="height: 3rem; width: auto;">' .
                '<div style="display: flex; flex-direction: column; line-height: 1.3;">' .
                '<span style="font-size: 0.75rem; font-weight: 400; letter-spacing: 0.05em;">PANEL ADMIN</span>' .
                '<span style="font-size: 0.95rem; font-weight: 600;">SMAN 1 NGRAHO</span>' .
                '</div>' .
                '</div>'
            ))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession; 
use App\Filament\App\Pages\Auth\EditProfile;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\MaxWidth;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
        ->maxContentWidth(MaxWidth::Full)
        ->sidebarCollapsibleOnDesktop()
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('assets/images/flaticon.png'))
            ->brandName('Admin Panel')
            ->unsavedChangesAlerts()
            ->profile(EditProfile::class)
            ->emailVerification()
            ->colors([
                'primary' => Color::Amber,
            ])
           ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
            ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
            // ->widgets([
            //     Widgets\AccountWidget::class,
            //     Widgets\FilamentInfoWidget::class,
            // ])
            ->widgets([
               
                \App\Filament\Widgets\ProjectsThisMonthChart::class, 
             
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            
           // Konfirmasi Sign Out dengan Pop-up SweetAlert2 (Anti-Hilang)
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString("
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    
                    <style>
                        .swal2-actions button {
                            color: #ffffff !important;
                            border-radius: 0.5rem !important;
                            padding: 0.5rem 1.5rem !important;
                            font-weight: 600 !important;
                            margin: 0 0.5rem !important;
                            border: none !important;
                            cursor: pointer !important;
                        }
                        .swal2-confirm {
                            background-color: #0d9488 !important; /* Warna Teal Filament Anda */
                        }
                        .swal2-confirm:hover {
                            background-color: #0f766e !important; /* Warna Teal Gelap saat disorot */
                        }
                        .swal2-cancel {
                            background-color: #ef4444 !important; /* Warna Merah Batal */
                        }
                        .swal2-cancel:hover {
                            background-color: #dc2626 !important; /* Warna Merah Gelap saat disorot */
                        }
                    </style>

                    <script>
                        document.addEventListener('submit', function(e) {
                            let form = e.target.closest('form');
                            if (form && form.action.includes('/logout')) {
                                e.preventDefault(); 
                                
                                Swal.fire({
                                    title: 'Sign Out',
                                    text: 'Are you sure you want to sign out?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, sign out',
                                    cancelButtonText: 'Cancel',
                                    // Matikan styling bawaan, agar pakai CSS pelindung di atas
                                    buttonsStyling: false, 
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit(); 
                                    }
                                });
                            }
                        });
                    </script>
                ")
            );
    }
}

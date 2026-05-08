<?php

namespace App\Providers\Filament;

use App\Actions\ResetDemoData;
use App\Actions\SeedDemoData;
use App\Enums\OrderStatus;
use App\Filament\Clusters\Products\Resources\Brands\BrandResource;
use App\Filament\Clusters\Products\Resources\Products\ProductResource;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Shop\Orders\OrderResource;
use App\Http\Middleware\Authenticate;
use App\Models\Shop\Brand;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Team;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JibayMcs\FilamentTour\FilamentTourPlugin;
use RalphJSmit\Filament\Activitylog\FilamentActivitylog;
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;
use RalphJSmit\Filament\MediaLibrary\Drivers\MediaLibraryItemDriver;
use RalphJSmit\Filament\MediaLibrary\FilamentMediaLibrary;
use RalphJSmit\Filament\Notifications\FilamentNotifications;
use RalphJSmit\Filament\MediaLibrary\Models\MediaLibraryItem;
use RalphJSmit\Filament\Onboard\FilamentOnboard;
use RalphJSmit\Filament\Onboard\Step;
use RalphJSmit\Filament\Onboard\Track;
use RalphJSmit\Filament\Onboard\Widgets\OnboardTrackWidget;
use RalphJSmit\Filament\Pulse\FilamentPulse;
use RalphJSmit\Filament\RecordFinder\FilamentRecordFinder;
use RalphJSmit\Filament\Upload\FilamentUpload;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->login(Login::class)
            ->tenant(Team::class)
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                OnboardTrackWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->unsavedChangesAlerts()
            ->brandLogo(asset('img/logo-dark.png'))
            ->darkModeBrandLogo(asset('img/logo-light.png'))
            ->brandLogoHeight('2rem')
            ->navigationGroups([
                'Shop',
                'Blog',
                'Monitoring',
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Reset demo data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->postAction(fn () => route('demo.reset', ['tenant' => Filament::getTenant()])),
            ])
            ->databaseNotifications()
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
            ->spa()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                FilamentMediaLibrary::make()
                    ->driver(MediaLibraryItemDriver::class, function (MediaLibraryItemDriver $driver): MediaLibraryItemDriver {
                        return $driver
                            ->conversions()
                            ->tenancy();
                    }),

                FilamentUpload::make(),

                FilamentActivitylog::make(),

                FilamentRecordFinder::make(),

                FilamentNotifications::make(),

                FilamentPulse::make()
                    ->navigationGroup('Monitoring')
                    ->navigationSort(99)
                    ->navigationLabel('Pulse')
                    ->navigationIcon('heroicon-o-chart-bar-square')
                    ->serversCard(sort: 1, columnSpan: 6)
                    ->usageCard(sort: 2, columnSpan: 6)
                    ->queuesCard(sort: 3, columnSpan: 6)
                    ->cacheCard(sort: 4, columnSpan: 6)
                    ->slowQueriesCard(sort: 5, columnSpan: 6)
                    ->exceptionsCard(sort: 6, columnSpan: 6)
                    ->slowRequestsCard(sort: 7, columnSpan: 6)
                    ->slowJobsCard(sort: 8, columnSpan: 6)
                    ->filamentPagesCard(sort: 9, columnSpan: 12),

                FilamentOnboard::make()
                    ->addTrack(fn () => Track::make([
                        Step::make('Add your first brand', 'add-brand')
                            ->description('Create a brand to organize your products')
                            ->icon('heroicon-o-building-storefront')
                            ->url(BrandResource::getUrl('create'))
                            ->completeIf(fn () => Brand::count() > 0),

                        Step::make('Create a product', 'create-product')
                            ->description('Add your first product to the catalog')
                            ->icon('heroicon-o-cube')
                            ->url(ProductResource::getUrl('create'))
                            ->completeIf(fn () => Product::count() > 0),

                        Step::make('Upload media', 'upload-media')
                            ->description('Add images to your media library')
                            ->icon('heroicon-o-photo')
                            ->completeIf(fn () => MediaLibraryItem::count() > 0),

                        Step::make('Process an order', 'process-order')
                            ->description('Update an order status to processing')
                            ->icon('heroicon-o-shopping-cart')
                            ->url(OrderResource::getUrl('index'))
                            ->completeIf(fn () => Order::where('status', '!=', OrderStatus::New)->exists()),

                        Step::make('Set up monitoring', 'setup-pulse')
                            ->description('Visit the Pulse dashboard to monitor your app')
                            ->icon('heroicon-o-chart-bar-square')
                            ->completeIf(fn () => true)
                            ->skippable(fn () => true),
                    ])->columns(3)->sequential(false)),

                FilamentAutoTranslator::make(),

                FilamentTourPlugin::make(),
            ]);
    }
}

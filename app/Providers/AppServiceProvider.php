<?php

namespace App\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Core\Cart\CurrentGuestToken;
use App\Models\Catalog\ProductVariant;
use App\Observers\ProductVariantObserver;
use App\Support\Macros\TextColumnMixin;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Table;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            \App\Core\Customer\CurrentCustomer::class,
            fn() => new \App\Core\Customer\CurrentCustomer()
        );
        $this->app->scoped(
            CurrentGuestToken::class,
            fn() => new CurrentGuestToken(request())
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();


        $this->configureTranslations();
        $this->configureMacros();

        ProductVariant::observe(ProductVariantObserver::class);
    }
    protected function configureMacros(): void
    {
        Table::macro('withImages', function () {
            /** @var Table $this */
            return $this->modifyQueryUsing(fn($query) => $query->with('images'));
        });

        TextColumn::mixin(new TextColumnMixin());
    }
    protected function configureTranslations(): void
    {
        Translatable::locales(['en', 'ar']);
        TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
            $component
                // locales labels
                ->localesLabels([
                    'ar' => __('app.ar'),
                    'en' => __('app.en')
                ])
                // default locales
                ->locales(['ar', 'en']);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}

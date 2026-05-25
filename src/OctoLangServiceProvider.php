<?php

namespace Johannhsdev\OctoLang;

use Johannhsdev\OctoLang\Console\InstallCommand;
use Johannhsdev\OctoLang\Console\StatusCommand;
use Johannhsdev\OctoLang\Console\UninstallCommand;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Johannhsdev\OctoLang\Http\Middleware\SetLocale;
use Johannhsdev\OctoLang\Setup\InstallManifest;
use Johannhsdev\OctoLang\Setup\OctoLangInstaller;
use Johannhsdev\OctoLang\Setup\Injectors\BladeInjector;
use Johannhsdev\OctoLang\Setup\Injectors\LivewireInjector;
use Johannhsdev\OctoLang\Setup\Injectors\ReactInjector;
use Johannhsdev\OctoLang\Setup\Injectors\SvelteInjector;
use Johannhsdev\OctoLang\Setup\Injectors\VueInjector;
use Johannhsdev\OctoLang\Setup\StackDetector;
use Johannhsdev\OctoLang\Setup\UninstallManager;
use Johannhsdev\OctoLang\Setup\WelcomeHandler;

class OctoLangServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/locale.php',
            'locale'
        );

        $this->app->singleton(LocaleManager::class, function ($app) {
            return new LocaleManager(
                $app->make(\Illuminate\Contracts\Config\Repository::class),
                $app->make(\Illuminate\Session\SessionManager::class),
            );
        });

        $this->app->singleton(InstallManifest::class, function ($app) {
            return new InstallManifest($app->basePath());
        });

        $this->app->singleton(StackDetector::class, function ($app) {
            return new StackDetector($app->basePath());
        });

        $this->app->singleton(WelcomeHandler::class, function ($app) {
            $stubsDir = __DIR__.'/../resources/stubs';
            $detector = $app->make(StackDetector::class);
            $manifest = $app->make(InstallManifest::class);

            return new WelcomeHandler(
                detector:  $detector,
                injectors: [
                    'blade'    => new BladeInjector($stubsDir, $detector, $manifest),
                    'livewire' => new LivewireInjector($stubsDir, $detector, $manifest),
                    'vue'      => new VueInjector($stubsDir, $detector, $manifest),
                    'react'    => new ReactInjector($stubsDir, $detector, $manifest),
                    'svelte'   => new SvelteInjector($stubsDir, $detector, $manifest),
                ],
                manifest: $manifest,
            );
        });

        $this->app->singleton(OctoLangInstaller::class, function ($app) {
            return new OctoLangInstaller(
                basePath: $app->basePath(),
                manifest: $app->make(InstallManifest::class),
                detector: $app->make(StackDetector::class),
            );
        });

        $this->app->singleton(UninstallManager::class, function ($app) {
            return new UninstallManager(
                basePath: $app->basePath(),
                manifest: $app->make(InstallManifest::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'octolang');

        $this->app['translator']->addNamespace('octolang', $this->app->langPath());

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'octolang');

        Blade::componentNamespace('Johannhsdev\\OctoLang\\View\\Components', 'octolang');

        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('octolang-switch', \Johannhsdev\OctoLang\Livewire\OctoLangSwitch::class);
        }

        $this->app['router']->pushMiddlewareToGroup('web', SetLocale::class);

        // Share OctoLang props with Inertia via lazy closures so they are
        // evaluated after SetLocale has set the application locale.
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share('locale', function () {
                return app(LocaleManager::class)->current();
            });

            \Inertia\Inertia::share('default_locale', function () {
                return app(LocaleManager::class)->default();
            });

            \Inertia\Inertia::share('locale_source', function () {
                return app(LocaleManager::class)->currentSource();
            });

            \Inertia\Inertia::share('supported_locales', function () {
                return app(LocaleManager::class)->supported();
            });

            \Inertia\Inertia::share('translations', function () {
                $manager = app(LocaleManager::class);
                $locale  = $manager->current();

                return app(SetLocale::class)->getTranslations($locale);
            });
        }

        AboutCommand::add('OctoLang', fn () => [
            'Version'   => '1.0.0',
            'Default'   => app(LocaleManager::class)->default(),
            'Supported' => implode(', ', config('locale.supported', [])),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                StatusCommand::class,
                UninstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/locale.php' => config_path('locale.php'),
            ], 'octolang-config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath(),
            ], 'octolang-lang');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/octolang'),
            ], 'octolang-views');

            $this->publishes([
                __DIR__.'/../resources/css' => resource_path('css'),
            ], 'octolang-css');
        }
    }
}

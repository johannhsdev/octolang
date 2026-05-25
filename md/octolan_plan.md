# 📦 Plan Maestro: Paquete `johannhsdev/octolang` — OctoLang
> **Versión:** 2.0 — Revisada contra documentación oficial Laravel 12.x  
> **Namespace:** `Johannhsdev\OctoLang`  
> **Soporte:** Laravel 11 / 12 — PHP 8.2+  
> **Estado actual:** Scaffolding creado, implementación PHP pendiente

---

## 🔴 Correcciones Urgentes Antes de Implementar

### 1. Mover carpeta `resources/lang` → `lang`

La documentación oficial de Laravel 12.x define que los archivos de traducción de paquetes deben vivir en `lang/` en la raíz, NO dentro de `resources/`:

```bash
# ❌ Como está ahora (INCORRECTO)
resources/lang/en/messages.php
resources/lang/es/messages.php

# ✅ Como debe quedar (CORRECTO)
lang/en/messages.php
lang/es/messages.php
```

**Acción:** Mover físicamente la carpeta y eliminar `resources/` si quedó vacía.

### 2. Agregar `composer.lock` al `.gitignore`

Los paquetes Composer **NO deben commitear** el `composer.lock`. Solo las aplicaciones lo hacen.

```bash
# .gitignore debe tener:
/vendor
composer.lock
/.phpunit.cache
```

---

## ✅ Estado Actual del Proyecto (Verificado en imagen)

| Archivo / Carpeta | Estado | Acción |
|---|---|---|
| `config/locale.php` | ✅ Creado | Rellenar contenido |
| `resources/lang/en/messages.php` | ❌ Ruta incorrecta | Mover a `lang/en/` |
| `resources/lang/es/messages.php` | ❌ Ruta incorrecta | Mover a `lang/es/` |
| `routes/web.php` | ✅ Creado | Rellenar contenido |
| `src/Facades/` | ✅ Carpeta existe | Crear `Locale.php` |
| `src/Http/Controllers/` | ✅ Carpeta existe | Crear `LocaleController.php` |
| `src/Http/Middleware/` | ✅ Carpeta existe | Crear `SetLocale.php` |
| `tests/Feature/` | ✅ Carpeta existe | Crear tests |
| `tests/Unit/` | ✅ Carpeta existe | Crear tests |
| `.github/` | ✅ Carpeta existe | Crear `workflows/run-tests.yml` |
| `composer.json` | ✅ Correcto | Sin cambios |
| `composer.lock` | ❌ No debe commitearse | Añadir al `.gitignore` |
| `src/LocaleManager.php` | ❌ Falta crear | Implementar |
| `src/OctoLangServiceProvider.php` | ❌ Falta crear | Implementar |
| `tests/TestCase.php` | ❌ Falta crear | Implementar |
| `tests/Pest.php` | ❌ Falta crear | Implementar |

---

## 📁 Estructura Final del Proyecto

```
octolang/
│
├── .claude/
├── .github/
│   └── workflows/
│       └── run-tests.yml
│
├── config/
│   └── locale.php
│
├── lang/                                    ← RAÍZ (no dentro de resources/)
│   ├── en/
│   │   └── messages.php
│   └── es/
│       └── messages.php
│
├── routes/
│   └── web.php
│
├── src/
│   ├── Facades/
│   │   └── Locale.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── LocaleController.php
│   │   └── Middleware/
│   │       └── SetLocale.php
│   ├── OctoLangServiceProvider.php
│   └── LocaleManager.php
│
├── tests/
│   ├── Feature/
│   │   └── LocaleControllerTest.php
│   ├── Unit/
│   │   └── LocaleManagerTest.php
│   ├── Pest.php
│   └── TestCase.php
│
├── vendor/
├── .gitignore
├── composer.json
├── LICENSE.md
└── README.md
```

---

## 📄 `composer.json` (Confirmado — Sin Cambios)

```json
{
    "name": "johannhsdev/octolang",
    "description": "A elegant Laravel package for managing application locales — OctoLang",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "JohannHSDev"
        }
    ],
    "require": {
        "php": "^8.2",
        "illuminate/support": "^11.0|^12.0",
        "illuminate/http": "^11.0|^12.0",
        "illuminate/routing": "^11.0|^12.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
        "pestphp/pest": "^2.0|^3.0"
    },
    "autoload": {
        "psr-4": {
            "Johannhsdev\\OctoLang\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Johannhsdev\\OctoLang\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Johannhsdev\\OctoLang\\OctoLangServiceProvider"
            ],
            "aliases": {
                "Locale": "Johannhsdev\\OctoLang\\Facades\\Locale"
            }
        }
    },
    "config": {
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

---

## 📄 Contenido de Cada Archivo a Implementar

### `config/locale.php`

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    | Used when no session locale exists or the stored locale is not supported.
    | Override via .env: LOCALE_DEFAULT=en
    */
    'default' => env('LOCALE_DEFAULT', 'es'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    | Comma-separated list in .env: LOCALE_SUPPORTED=es,en,fr,pt
    */
    'supported' => array_filter(
        explode(',', env('LOCALE_SUPPORTED', 'es,en'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    */
    'session_key' => env('LOCALE_SESSION_KEY', 'locale'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route_name'       => 'locale.store',
    'route_uri'        => '/locale',
    'route_middleware' => ['web'],

];
```

---

### `lang/en/messages.php`

```php
<?php

return [
    'invalid_locale' => 'The selected locale is not supported.',
];
```

### `lang/es/messages.php`

```php
<?php

return [
    'invalid_locale' => 'El idioma seleccionado no está soportado.',
];
```

---

### `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Johannhsdev\OctoLang\Http\Controllers\LocaleController;

Route::post(
    config('locale.route_uri', '/locale'),
    [LocaleController::class, 'store']
)
->middleware(config('locale.route_middleware', ['web']))
->name(config('locale.route_name', 'locale.store'));
```

---

### `src/LocaleManager.php`

```php
<?php

namespace Johannhsdev\OctoLang;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\App;

class LocaleManager
{
    public function __construct(
        protected Config  $config,
        protected Session $session,
    ) {}

    /**
     * Persist a locale in session if it is supported.
     */
    public function set(string $locale): void
    {
        if ($this->isSupported($locale)) {
            $this->session->put($this->sessionKey(), $locale);
        }
    }

    /**
     * Return the active locale from session, or fall back to default.
     */
    public function current(): string
    {
        $locale = $this->session->get($this->sessionKey(), $this->default());

        return $this->isSupported($locale) ? $locale : $this->default();
    }

    /**
     * Apply the current locale to the Laravel application.
     */
    public function apply(): void
    {
        App::setLocale($this->current());
    }

    /**
     * Check if a locale is in the supported list.
     */
    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supported(), strict: true);
    }

    /**
     * Return all supported locales.
     */
    public function supported(): array
    {
        return (array) $this->config->get('locale.supported', []);
    }

    /**
     * Return the configured default locale.
     */
    public function default(): string
    {
        return (string) $this->config->get('locale.default', 'es');
    }

    // -------------------------------------------------------------------------

    private function sessionKey(): string
    {
        return (string) $this->config->get('locale.session_key', 'locale');
    }
}
```

---

### `src/OctoLangServiceProvider.php`

```php
<?php

namespace Johannhsdev\OctoLang;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Johannhsdev\OctoLang\Http\Middleware\SetLocale;

class OctoLangServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config as fallback (user's published config takes priority)
        $this->mergeConfigFrom(
            __DIR__.'/../config/locale.php',
            'locale'
        );

        // Bind LocaleManager as singleton in the container
        $this->app->singleton(LocaleManager::class, function ($app) {
            return new LocaleManager(
                $app['config'],
                $app['session.store'],
            );
        });
    }

    public function boot(): void
    {
        // Register package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register package translation files
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'octolang');

        // Inject SetLocale middleware into the 'web' group automatically
        // Works on Laravel 11/12 without touching Kernel.php or bootstrap/app.php
        $this->app['router']->pushMiddlewareToGroup('web', SetLocale::class);

        // Show package info in `php artisan about`
        AboutCommand::add('OctoLang', fn () => [
            'Version'   => '1.0.0',
            'Default'   => config('locale.default', 'es'),
            'Supported' => implode(', ', config('locale.supported', [])),
        ]);

        // Only register publishable resources when running in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/locale.php' => config_path('locale.php'),
            ], 'locale-config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/octolang'),
            ], 'locale-lang');
        }
    }
}
```

---

### `src/Http/Middleware/SetLocale.php`

```php
<?php

namespace Johannhsdev\OctoLang\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Johannhsdev\OctoLang\LocaleManager;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(protected LocaleManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->manager->apply();

        return $next($request);
    }
}
```

---

### `src/Http/Controllers/LocaleController.php`

```php
<?php

namespace Johannhsdev\OctoLang\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Johannhsdev\OctoLang\LocaleManager;

class LocaleController extends Controller
{
    public function __construct(protected LocaleManager $manager) {}

    public function store(Request $request): RedirectResponse
    {
        $this->manager->set(
            $request->input('locale', '')
        );

        return redirect()->back();
    }
}
```

---

### `src/Facades/Locale.php`

```php
<?php

namespace Johannhsdev\OctoLang\Facades;

use Illuminate\Support\Facades\Facade;
use Johannhsdev\OctoLang\LocaleManager;

/**
 * @method static void   set(string $locale)
 * @method static string current()
 * @method static void   apply()
 * @method static bool   isSupported(string $locale)
 * @method static array  supported()
 * @method static string default()
 *
 * @see \Johannhsdev\OctoLang\LocaleManager
 */
class Locale extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LocaleManager::class;
    }
}
```

---

### `tests/TestCase.php`

```php
<?php

namespace Johannhsdev\OctoLang\Tests;

use Johannhsdev\OctoLang\OctoLangServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            OctoLangServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('locale.default', 'es');
        $app['config']->set('locale.supported', ['es', 'en', 'fr']);
        $app['config']->set('locale.session_key', 'locale');
        $app['config']->set('locale.route_uri', '/locale');
        $app['config']->set('locale.route_name', 'locale.store');
        $app['config']->set('locale.route_middleware', ['web']);
    }
}
```

### `tests/Pest.php`

```php
<?php

uses(Johannhsdev\OctoLang\Tests\TestCase::class)->in('Unit', 'Feature');
```

---

### `tests/Unit/LocaleManagerTest.php`

```php
<?php

use Johannhsdev\OctoLang\LocaleManager;

it('returns the default locale when no session is set', function () {
    $manager = app(LocaleManager::class);

    expect($manager->current())->toBe('es');
});

it('sets a supported locale in session', function () {
    $manager = app(LocaleManager::class);
    $manager->set('en');

    expect($manager->current())->toBe('en');
});

it('ignores unsupported locales', function () {
    $manager = app(LocaleManager::class);
    $manager->set('jp');

    expect($manager->current())->toBe('es');
});

it('returns true for supported locales', function () {
    $manager = app(LocaleManager::class);

    expect($manager->isSupported('en'))->toBeTrue();
    expect($manager->isSupported('fr'))->toBeTrue();
});

it('returns false for unsupported locales', function () {
    $manager = app(LocaleManager::class);

    expect($manager->isSupported('jp'))->toBeFalse();
});

it('returns all supported locales', function () {
    $manager = app(LocaleManager::class);

    expect($manager->supported())->toBe(['es', 'en', 'fr']);
});

it('returns the configured default locale', function () {
    $manager = app(LocaleManager::class);

    expect($manager->default())->toBe('es');
});

it('applies the locale to the Laravel app', function () {
    $manager = app(LocaleManager::class);
    $manager->set('en');
    $manager->apply();

    expect(app()->getLocale())->toBe('en');
});
```

### `tests/Feature/LocaleControllerTest.php`

```php
<?php

it('stores a valid locale and redirects back', function () {
    $this->post('/locale', ['locale' => 'en'])
        ->assertRedirect();

    expect(session('locale'))->toBe('en');
});

it('does not store an invalid locale', function () {
    $this->post('/locale', ['locale' => 'jp'])
        ->assertRedirect();

    expect(session('locale'))->toBeNull();
});

it('applies the default locale via middleware when no session exists', function () {
    $this->get('/');

    expect(app()->getLocale())->toBe('es');
});

it('applies the session locale via middleware', function () {
    session(['locale' => 'fr']);

    $this->get('/');

    expect(app()->getLocale())->toBe('fr');
});

it('falls back to default when session has unsupported locale', function () {
    session(['locale' => 'jp']);

    $this->get('/');

    expect(app()->getLocale())->toBe('es');
});
```

---

### `.github/workflows/run-tests.yml`

```yaml
name: OctoLang Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3']
        laravel: ['11.*', '12.*']

    name: PHP ${{ matrix.php }} — Laravel ${{ matrix.laravel }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: mbstring, pdo
          coverage: none

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction --no-progress

      - name: Run tests
        run: ./vendor/bin/pest --ci
```

---

### `.gitignore` (Correcto)

```
/vendor
composer.lock
/.phpunit.cache
```

---

## 📋 Orden de Implementación para Claude Code

Ejecutar en este orden exacto para evitar errores de dependencias entre clases:

```
1.  Corregir .gitignore          → añadir composer.lock
2.  Mover resources/lang → lang  → mover carpetas físicamente
3.  Eliminar carpeta resources/  → si quedó vacía
4.  config/locale.php            → rellenar contenido
5.  lang/en/messages.php         → rellenar contenido
6.  lang/es/messages.php         → rellenar contenido
7.  routes/web.php               → rellenar contenido
8.  src/LocaleManager.php        → clase principal
9.  src/OctoLangServiceProvider.php
10. src/Http/Middleware/SetLocale.php
11. src/Http/Controllers/LocaleController.php
12. src/Facades/Locale.php
13. tests/TestCase.php
14. tests/Pest.php
15. tests/Unit/LocaleManagerTest.php
16. tests/Feature/LocaleControllerTest.php
17. .github/workflows/run-tests.yml
18. Ejecutar: ./vendor/bin/pest   → verificar verde
```

---

## 🔌 Cómo Instalar en un Proyecto Laravel (Post-Publicación)

```bash
composer require johannhsdev/octolang
php artisan vendor:publish --tag=locale-config
```

Añadir al `.env` del proyecto:
```env
LOCALE_DEFAULT=es
LOCALE_SUPPORTED=es,en,fr
```

Usar en Blade:
```html
<form method="POST" action="{{ route('locale.store') }}">
    @csrf
    <button name="locale" value="en">English</button>
    <button name="locale" value="es">Español</button>
</form>

{{-- Con Facade --}}
<span>{{ Locale::current() }}</span>
```

Verificar en consola:
```bash
php artisan about
```

---

## 📦 Checklist Final para Publicar en Packagist

- [ ] Todos los archivos PHP creados e implementados
- [ ] `./vendor/bin/pest` pasa al 100%
- [ ] CI verde en GitHub Actions (PHP 8.2/8.3 × Laravel 11/12)
- [ ] `config/locale.php` publicable con `--tag=locale-config`
- [ ] `lang/` publicable con `--tag=locale-lang`
- [ ] `php artisan about` muestra la sección OctoLang
- [ ] `README.md` completo con badges, instalación y ejemplos
- [ ] `CHANGELOG.md` con entry para v1.0.0
- [ ] `LICENSE.md` presente (MIT)
- [ ] `composer.lock` NO commiteado
- [ ] `git tag v1.0.0 && git push origin v1.0.0`
- [ ] Registrado en packagist.org con webhook activo
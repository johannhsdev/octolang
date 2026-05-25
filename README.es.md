# OctoLang 🐙

OctoLang es un paquete para Laravel que te permite gestionar el cambio de idioma de forma simple, con un flujo de instalación explícito, un selector visual listo para usar y un proceso de desinstalación seguro.

Está pensado principalmente para proyectos Blade, pero también puede usarse con Livewire, Vue, React y Svelte si Laravel sigue siendo la fuente de verdad del idioma activo.

## Qué hace el paquete

OctoLang incluye:

- Un servicio `LocaleManager` para guardar y resolver el locale actual desde la sesión
- Un middleware `SetLocale` que aplica el idioma activo en cada request
- Una ruta `POST` para cambiar de idioma de forma segura
- Un componente Blade: `<x-octolang::locale-switcher />`
- Comandos de instalación, estado y desinstalación
- Un manifest interno para eliminar únicamente lo que OctoLang haya creado o modificado

Durante la instalación, OctoLang puede crear o actualizar estos recursos según el stack detectado:

| Recurso | Blade | Livewire | Vue | React | Svelte |
|---------|:-----:|:--------:|:---:|:-----:|:------:|
| `resources/views/welcome.blade.php` (inyección) | ✓ | ✓ | — | — | — |
| `resources/css/octolang.css` | ✓ | ✓ | ✓ | ✓ | ✓ |
| `resources/css/app.css` (`@import`) | ✓ | ✓ | ✓ | ✓ | ✓ |
| `resources/views/vendor/octolang/components/locale-switcher.blade.php` | ✓ | — | — | — | — |
| `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` | — | ✓ | — | — | — |
| `resources/js/components/octolang/OctoLangSwitch.vue` + `useOctoLang.ts` | — | — | ✓ | — | — |
| `resources/js/components/octolang/LocaleSwitcher.tsx` + `useOctoLang.ts` | — | — | — | ✓ | — |
| `resources/js/components/octolang/OctoLangSwitch.svelte` | — | — | — | — | ✓ |
| `resources/js/stores/useOctoLang.ts` | — | — | — | — | ✓ |
| `lang/en/messages.php` y `lang/es/messages.php` | ✓ | ✓ | ✓ | ✓ | ✓ |
| `storage/app/octolang/install.json` | ✓ | ✓ | ✓ | ✓ | ✓ |

## Requisitos

- PHP `^8.2|^8.4`
- Laravel `^11.0|^12.0|^13.0`

## Instalación

### 1. Instalar desde Packagist

Si el paquete ya está publicado, instálalo normalmente:

```bash
composer require johannhsdev/octolang
```

### 2. Ejecutar la instalación de OctoLang

Después de `composer require`, ejecuta:

```bash
php artisan octolang:install
php artisan optimize:clear
```

Si tu proyecto usa Vite, recompila los assets:

```bash
npm run dev
```

o:

```bash
npm run build
```

## Qué hace `php artisan octolang:install`

El comando de instalación ejecuta el setup del paquete de forma explícita. No corre automáticamente en cada request.

Este comando:

1. Copia `lang/en/messages.php` y `lang/es/messages.php` solo si esos archivos no existen
2. Copia `resources/views/vendor/octolang/components/locale-switcher.blade.php` si hace falta
3. Copia `resources/css/octolang.css` si hace falta
4. Agrega `@import "./octolang.css";` en `resources/css/app.css` si el archivo existe y todavía no contiene esa línea
5. Procesa `resources/views/welcome.blade.php`
6. Registra archivos creados y cambios rastreados en `storage/app/octolang/install.json`

## Comandos disponibles

### `php artisan octolang:install`

Úsalo después de instalar el paquete con Composer.

Cuándo usarlo:

- cuando instalas OctoLang por primera vez
- cuando quieres regenerar recursos faltantes del paquete
- cuando quieres que OctoLang procese la vista `welcome`

### `php artisan octolang:status`

Muestra los archivos y mutaciones que OctoLang tiene registrados.

Cuándo usarlo:

- cuando quieres auditar qué creó OctoLang
- cuando quieres saber qué podrá eliminar `octolang:uninstall`

### `php artisan octolang:uninstall`

Elimina únicamente los archivos y cambios que OctoLang tiene registrados.

Cuándo usarlo:

- cuando quieres desmontar el paquete antes de hacer `composer remove`
- cuando estás probando el flujo de instalación y desinstalación

Flujo recomendado para desinstalar:

```bash
php artisan octolang:uninstall
composer remove johannhsdev/octolang
```

## Configuración

Si quieres editar la configuración manualmente, publícala con:

```bash
php artisan vendor:publish --tag=octolang-config
```

Configuración por defecto:

```php
return [
    'supported' => array_filter(
        explode(',', env('LOCALE_SUPPORTED', 'es,en'))
    ),
    'session_key' => env('LOCALE_SESSION_KEY', 'locale'),
    'route_name' => 'locale.store',
    'route_uri' => '/locale',
    'route_middleware' => ['web'],
];
```

### Ejemplo de `.env`

```env
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOCALE_SUPPORTED=es,en,fr,de
LOCALE_SESSION_KEY=locale
```

Notas importantes:

- `APP_LOCALE` es la fuente de verdad para el idioma inicial de OctoLang.
- `LOCALE_SESSION_KEY` solo cambia la clave usada en `session()`.
- `APP_FALLBACK_LOCALE` sigue siendo responsabilidad de Laravel para traducciones faltantes.
- `APP_FAKER_LOCALE` solo afecta Faker y no participa en el idioma visible de la aplicacion.
- `LOCALE_SUPPORTED` valida los cambios hechos desde el switcher, pero no reemplaza ni reordena el `APP_LOCALE` base.

## Idiomas soportados

OctoLang valida los cambios de idioma contra `locale.supported`.

Si quieres agregar más idiomas, actualiza tu configuración o tu `.env`, por ejemplo:

```env
LOCALE_SUPPORTED=es,en,fr,pt,de,it
```

Luego crea tus archivos de traducción:

```text
lang/
  en/
    messages.php
  es/
    messages.php
  fr/
    messages.php
```

Ejemplo:

```php
// lang/en/messages.php
return [
    'welcome' => 'Welcome',
    'dashboard' => 'Dashboard',
    'settings' => 'Settings',
];
```

```php
// lang/es/messages.php
return [
    'welcome' => 'Bienvenido',
    'dashboard' => 'Panel',
    'settings' => 'Configuración',
];
```

## Cómo funciona el cambio de idioma

OctoLang guarda el idioma seleccionado en sesión y lo aplica a través de middleware.

Flujo:

1. El usuario envía un locale a la ruta de OctoLang
2. `LocaleController` valida ese locale contra `locale.supported`
3. `LocaleManager` lo guarda en la sesión
4. `SetLocale` ejecuta `App::setLocale(...)` en el siguiente request usando `APP_LOCALE` como base
5. Laravel resuelve las traducciones usando el locale activo

Cuando el usuario cambia a un idioma distinto del default, OctoLang lo fija en sesión.
Cuando vuelve al idioma default, OctoLang limpia esa clave de sesión para que la app vuelva a seguir `APP_LOCALE`.

En el selector visual, el idioma activo sigue marcado como activo en ambos casos.
Cuando el locale activo proviene del default de la app y no de una preferencia guardada, OctoLang agrega una marca visual sutil para indicar que ese idioma esta siguiendo la configuracion base.

## Uso en Blade

### Agregar el selector

Puedes colocar el componente en cualquier vista Blade:

```blade
<x-octolang::locale-switcher />
```

Ejemplo en un navbar:

```blade
<header class="flex items-center justify-between px-6 py-4">
    <div>
        <a href="/">{{ config('app.name') }}</a>
    </div>

    <nav class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
        <a href="{{ route('profile') }}">{{ __('messages.profile') }}</a>
        <x-octolang::locale-switcher />
    </nav>
</header>
```

Ejemplo en un sidebar:

```blade
<aside class="w-64 p-4 space-y-4">
    <x-octolang::locale-switcher />

    <nav class="space-y-2">
        <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
        <a href="{{ route('settings') }}">{{ __('messages.settings') }}</a>
        <a href="{{ route('users.index') }}">{{ __('messages.users') }}</a>
    </nav>
</aside>
```

### Usar traducciones en textos Blade

Usa los helpers normales de traducción de Laravel:

```blade
<h1>{{ __('messages.welcome') }}</h1>
<p>{{ __('messages.dashboard_description') }}</p>
```

Con reemplazos:

```blade
<p>{{ __('messages.greeting', ['name' => $user->name]) }}</p>
```

Pluralización:

```blade
<p>{{ trans_choice('messages.notifications', $count, ['count' => $count]) }}</p>
```

### Claves de traducción del paquete

OctoLang también expone claves namespaced como estas:

```blade
{{ __('octolang::messages.switcher.label') }}
{{ __('octolang::messages.switcher.tooltip') }}
```

En tus vistas normales, lo ideal es que uses tus propias claves de aplicación como `__('messages.welcome')`.

## Uso en Livewire

OctoLang incluye un componente Livewire v3 nativo: `<livewire:octolang-switch />`.

Se registra automáticamente si Livewire está instalado. No necesitas hacer nada extra en el backend.

### Qué instala `octolang:install` en un proyecto Livewire

Al detectar Livewire, el comando crea:

- `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` — vista publicable del componente
- `resources/css/octolang.css` y el import en `app.css`
- Inyecta `<livewire:octolang-switch />` en `welcome.blade.php` sin reemplazar su contenido

> En proyectos Livewire **no** se copia `components/locale-switcher.blade.php` — ese archivo solo pertenece al stack Blade puro.

### Agregar el selector

Coloca el componente en cualquier vista Blade dentro de tu app Livewire:

```blade
<livewire:octolang-switch />
```

Ejemplo en un layout:

```blade
<header class="flex items-center justify-between px-6 py-4">
    <a href="/">{{ config('app.name') }}</a>

    <nav class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
        <livewire:octolang-switch />
    </nav>
</header>
```

### Cómo funciona el cambio de idioma

1. El usuario hace click en un botón del selector
2. Livewire llama la acción `switchLocale($locale)` del componente vía AJAX
3. `LocaleManager::set()` guarda el nuevo locale en sesión
4. El componente dispara el evento `octolang:locale-changed`
5. El script del componente escucha ese evento y ejecuta `window.location.reload()`
6. El browser recarga la página — el middleware `SetLocale` aplica el nuevo locale a toda la vista

Este flujo garantiza que todos los `__()` del welcome y de cualquier otra vista se re-rendericen con el idioma correcto.

### Usar traducciones en tus vistas

Las traducciones funcionan igual que en Blade puro:

```blade
<h1>{{ __('messages.welcome') }}</h1>
<p>{{ __('messages.dashboard_description') }}</p>
```

Con las claves del paquete:

```blade
{{ __('octolang::messages.switcher.label') }}
{{ __('octolang::messages.switcher.tooltip') }}
```

### Personalizar la vista del componente

Si necesitas ajustar el HTML o los estilos del selector, publica la vista:

```bash
php artisan vendor:publish --tag=octolang-views
```

Esto copia la vista a `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` donde puedes editarla libremente sin tocar el paquete.

## Uso en Vue (Inertia.js)

OctoLang incluye soporte nativo para Vue 3 con Inertia.js. El middleware `SetLocale` comparte automáticamente el locale activo y las traducciones como props de Inertia — no necesitas hacer nada en el backend más allá de instalar el paquete.

### Qué instala `octolang:install` en un proyecto Vue

Al detectar un proyecto Vue/Inertia, el comando crea:

- `resources/js/components/octolang/OctoLangSwitch.vue` — componente listo para usar
- `resources/js/composables/useOctoLang.ts` — composable para acceder a traducciones y cambiar locale
- `resources/css/octolang.css` y el import en `app.css`

Los archivos generados tienen el marcador `// octolang:processed` para que reinstalaciones no los sobreescriban.

### Props compartidos automáticamente

El middleware inyecta en cada respuesta Inertia:

| Prop | Tipo | Descripción |
|------|------|-------------|
| `locale` | `string` | Locale activo (`"es"`, `"en"`, etc.) |
| `supported_locales` | `string[]` | Locales habilitados en config |
| `translations` | `object` | Archivos de traducción del namespace `octolang::` |

### El composable `useOctoLang`

El composable generado en `resources/js/composables/useOctoLang.ts` expone:

```ts
const { locale, supported_locales, __, switchLocale } = useOctoLang()
```

- `locale` — computed reactivo con el locale activo
- `supported_locales` — computed reactivo con los locales disponibles
- `__('messages.welcome.title')` — función de traducción equivalente a `__('octolang::messages.welcome.title')` en Blade
- `switchLocale('en')` — hace `POST /locale` y Inertia recarga la página con el nuevo idioma

### Usar traducciones en un componente

```vue
<script setup lang="ts">
import { useOctoLang } from '@/composables/useOctoLang'

const { __ } = useOctoLang()
</script>

<template>
    <section>
        <h1>{{ __('messages.welcome.title') }}</h1>
        <p>{{ __('messages.welcome.octolang_status') }}</p>
    </section>
</template>
```

La clave sigue el formato `archivo.grupo.clave`. El primer segmento es el nombre del archivo PHP en `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 archivo   grupo   clave
```

Si el primer segmento no coincide con ningún archivo conocido, `useOctoLang` asume `messages` por defecto:

```ts
__('welcome.title')  // equivale a __('messages.welcome.title')
```

### El componente `OctoLangSwitch`

El componente generado en `resources/js/components/octolang/OctoLangSwitch.vue` renderiza un `<nav>` con un botón por cada locale soportado. No recibe props — los datos vienen automáticamente del composable.

```vue
<script setup lang="ts">
import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'
</script>

<template>
    <nav class="flex items-center gap-4">
        <OctoLangSwitch />
        <!-- resto de tu nav -->
    </nav>
</template>
```

Al hacer click en un botón, llama `POST /locale` y Inertia recarga la página con las traducciones actualizadas de forma reactiva.

### Integración en `welcome.vue`

El instalador detecta tu `welcome.vue` y **no lo reemplaza** — solo inyecta el import, el composable y el componente. Así queda una integración típica:

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login, register } from '@/routes'
// octolang:start
import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'
import { useOctoLang } from '@/composables/useOctoLang'
const { __ } = useOctoLang()
// octolang:end

withDefaults(defineProps<{ canRegister: boolean }>(), { canRegister: true })
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center ...">
        <header class="...">
            <nav class="flex items-center justify-end gap-4">
                <!-- octolang:start -->
                <OctoLangSwitch />
                <!-- octolang:end -->
                <Link v-if="$page.props.auth.user" :href="dashboard()">Dashboard</Link>
                <template v-else>
                    <Link :href="login()">Log in</Link>
                    <Link v-if="canRegister" :href="register()">Register</Link>
                </template>
            </nav>
        </header>

        <!-- Bloque de mensajes traducidos -->
        <!-- octolang:block:start -->
        <div class="w-full max-w-4xl text-center mb-6">
            <p class="text-base font-medium">
                {{ __('messages.welcome.octolang_thanks') }}
            </p>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                {{ __('messages.welcome.octolang_status') }}
            </p>
        </div>
        <!-- octolang:block:end -->

        <!-- resto de tu welcome -->
    </div>
</template>
```

### Añadir tus propias traducciones

Crea o edita `lang/es/messages.php` y `lang/en/messages.php` en la raíz de tu proyecto:

```php
// lang/es/messages.php
return [
    'nav' => [
        'home'      => 'Inicio',
        'dashboard' => 'Panel',
        'logout'    => 'Cerrar sesión',
    ],
    'hero' => [
        'title'    => 'Bienvenido a mi app',
        'subtitle' => 'La mejor plataforma del mercado',
    ],
];
```

```php
// lang/en/messages.php
return [
    'nav' => [
        'home'      => 'Home',
        'dashboard' => 'Dashboard',
        'logout'    => 'Log out',
    ],
    'hero' => [
        'title'    => 'Welcome to my app',
        'subtitle' => 'The best platform on the market',
    ],
];
```

Úsalas en Vue:

```vue
<script setup lang="ts">
import { useOctoLang } from '@/composables/useOctoLang'

const { __ } = useOctoLang()
</script>

<template>
    <h1>{{ __('messages.hero.title') }}</h1>
    <a href="/dashboard">{{ __('messages.nav.dashboard') }}</a>
</template>
```

## Uso en React (Inertia.js)

OctoLang incluye soporte nativo para React con Inertia.js. El middleware `SetLocale` comparte automáticamente el locale activo y las traducciones como props de Inertia — no necesitas hacer nada en el backend más allá de instalar el paquete.

### Qué instala `octolang:install` en un proyecto React

Al detectar un proyecto React/Inertia, el comando crea:

- `resources/js/components/octolang/LocaleSwitcher.tsx` — componente listo para usar
- `resources/js/hooks/useOctoLang.ts` — hook para acceder a traducciones y cambiar locale
- `resources/css/octolang.css` y el import en `app.css`

Los archivos generados tienen el marcador `// octolang:processed` para que reinstalaciones no los sobreescriban.

### Props compartidos automáticamente

El middleware inyecta en cada respuesta Inertia:

| Prop | Tipo | Descripción |
|------|------|-------------|
| `locale` | `string` | Locale activo (`"es"`, `"en"`, etc.) |
| `supported_locales` | `string[]` | Locales habilitados en config |
| `translations` | `object` | Archivos de traducción del namespace `octolang::` |

### El hook `useOctoLang`

El hook generado en `resources/js/hooks/useOctoLang.ts` expone:

```ts
const { locale, supported_locales, __, switchLocale } = useOctoLang()
```

- `locale` — string con el locale activo
- `supported_locales` — array de locales disponibles
- `__('messages.welcome.title')` — función de traducción equivalente a `__('octolang::messages.welcome.title')` en Blade
- `switchLocale('en')` — hace `POST /locale` y recarga la página con Inertia

### Usar traducciones en un componente

```tsx
import { useOctoLang } from '@/hooks/useOctoLang'

export default function HeroBanner() {
    const { __ } = useOctoLang()

    return (
        <section>
            <h1>{__('messages.welcome.title')}</h1>
            <p>{__('messages.welcome.description')}</p>
        </section>
    )
}
```

La clave sigue el formato `archivo.grupo.clave`. El primer segmento es el nombre del archivo PHP en `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 archivo   grupo   clave
```

Si el primer segmento no coincide con ningún archivo conocido, `useOctoLang` asume `messages` por defecto:

```ts
__('welcome.title')  // equivale a __('messages.welcome.title')
```

### El componente `LocaleSwitcher`

El componente generado en `resources/js/components/octolang/LocaleSwitcher.tsx` renderiza un `<nav>` con un botón por cada locale soportado. No recibe props — los datos vienen automáticamente del hook.

```tsx
import LocaleSwitcher from '@/components/octolang/LocaleSwitcher'

// Úsalo donde quieras, normalmente en el header o nav principal
<nav className="flex items-center gap-4">
    <LocaleSwitcher />
    {/* resto de tu nav */}
</nav>
```

Al hacer click en un botón, llama `POST /locale` y Inertia recarga la página con las traducciones actualizadas.

### Integración en `welcome.tsx`

El instalador detecta tu `welcome.tsx` y **no lo reemplaza** — solo inyecta el import y el hook. Así queda una integración típica:

```tsx
import { Head, Link, usePage } from '@inertiajs/react'
import { dashboard, login, register } from '@/routes'
import type { SharedData } from '@/types'
import LocaleSwitcher from '@/components/octolang/LocaleSwitcher'
import { useOctoLang } from '@/hooks/useOctoLang'

export default function Welcome({ canRegister = true }: { canRegister?: boolean }) {
    const { auth } = usePage<SharedData>().props
    const { __ } = useOctoLang()

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col items-center ...">
                <header className="...">
                    <nav className="flex items-center justify-end gap-4">
                        <LocaleSwitcher />
                        {auth.user ? (
                            <Link href={dashboard()}>Dashboard</Link>
                        ) : (
                            <>
                                <Link href={login()}>Log in</Link>
                                {canRegister && <Link href={register()}>Register</Link>}
                            </>
                        )}
                    </nav>
                </header>

                {/* Bloque de mensajes traducidos */}
                <div className="w-full max-w-4xl text-center mb-6">
                    <p className="text-base font-medium">
                        {__('messages.welcome.octolang_thanks')}
                    </p>
                    <p className="text-sm text-[#706f6c]">
                        {__('messages.welcome.octolang_status')}
                    </p>
                </div>

                {/* resto de tu welcome */}
            </div>
        </>
    )
}
```

### Añadir tus propias traducciones

Crea o edita `lang/es/messages.php` y `lang/en/messages.php` en la raíz de tu proyecto:

```php
// lang/es/messages.php
return [
    'nav' => [
        'home'      => 'Inicio',
        'dashboard' => 'Panel',
        'logout'    => 'Cerrar sesión',
    ],
    'hero' => [
        'title'    => 'Bienvenido a mi app',
        'subtitle' => 'La mejor plataforma del mercado',
    ],
];
```

```php
// lang/en/messages.php
return [
    'nav' => [
        'home'      => 'Home',
        'dashboard' => 'Dashboard',
        'logout'    => 'Log out',
    ],
    'hero' => [
        'title'    => 'Welcome to my app',
        'subtitle' => 'The best platform on the market',
    ],
];
```

Úsalas en React:

```tsx
const { __ } = useOctoLang()

<h1>{__('messages.hero.title')}</h1>
<a href="/dashboard">{__('messages.nav.dashboard')}</a>
```

### Nota sobre Windows

En Windows, OctoLang usa `dirname(__DIR__, 3)` y `str_replace('\\', '/', $dir)` internamente para resolver los paths de los archivos de traducción con `glob()`. Esto es transparente para el usuario — mencionado aquí solo como referencia de compatibilidad.

## Uso en Svelte (Inertia.js)

OctoLang incluye soporte nativo para Svelte 5 con Inertia.js. El middleware `SetLocale` comparte automáticamente el locale activo y las traducciones como props de Inertia — no necesitas hacer nada en el backend más allá de instalar el paquete.

### Qué instala `octolang:install` en un proyecto Svelte

Al detectar un proyecto Svelte/Inertia, el comando crea:

- `resources/js/components/octolang/OctoLangSwitch.svelte` — componente listo para usar
- `resources/js/stores/useOctoLang.ts` — store con acceso a traducciones y cambio de locale
- `resources/css/octolang.css` y el import en `app.css`

Los archivos generados tienen el marcador `// octolang:processed` para que reinstalaciones no los sobreescriban.

### Props compartidos automáticamente

El middleware inyecta en cada respuesta Inertia:

| Prop | Tipo | Descripción |
|------|------|-------------|
| `locale` | `string` | Locale activo (`"es"`, `"en"`, etc.) |
| `supported_locales` | `string[]` | Locales habilitados en config |
| `translations` | `object` | Archivos de traducción del namespace `octolang::` |

### El store `useOctoLang`

El store generado en `resources/js/stores/useOctoLang.ts` expone:

```ts
import { getLocale, getSupportedLocales, __, switchLocale } from '@/stores/useOctoLang'
```

- `getLocale()` — devuelve el locale activo, reactivo vía `$derived` en componentes
- `getSupportedLocales()` — devuelve el array de locales disponibles
- `__('messages.welcome.title')` — función de traducción equivalente a `__('octolang::messages.welcome.title')` en Blade
- `switchLocale('en')` — hace `POST /locale` y recarga la página con el nuevo idioma

> **Nota Svelte 5:** `page` de `@inertiajs/svelte` es un objeto `$state` rune, no un store de `svelte/store`. Por eso el store de OctoLang expone getters en vez de valores derivados con `derived()`. Úsalos siempre dentro de `$derived()` en tus componentes para mantener la reactividad.

### Usar traducciones en un componente

```svelte
<script lang="ts">
    import { __ } from '@/stores/useOctoLang'
</script>

<section>
    <h1>{__('messages.welcome.title')}</h1>
    <p>{__('messages.welcome.octolang_status')}</p>
</section>
```

La clave sigue el formato `archivo.grupo.clave`. El primer segmento es el nombre del archivo PHP en `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 archivo   grupo   clave
```

Si el primer segmento no coincide con ningún archivo conocido, `useOctoLang` asume `messages` por defecto:

```ts
__('welcome.title')  // equivale a __('messages.welcome.title')
```

### El componente `OctoLangSwitch`

El componente generado en `resources/js/components/octolang/OctoLangSwitch.svelte` renderiza un `<nav>` con un botón por cada locale soportado. Maneja su propia reactividad internamente — no recibe props.

```svelte
<script lang="ts">
    import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.svelte'
</script>

<nav class="flex items-center gap-4">
    <OctoLangSwitch />
    <!-- resto de tu nav -->
</nav>
```

Al hacer click en un botón, llama `POST /locale` y la página se recarga con las traducciones actualizadas.

### Acceso reactivo a locale en tus componentes

Cuando necesites reaccionar al locale activo (por ejemplo para lógica condicional), usa `$derived` con el getter:

```svelte
<script lang="ts">
    import { getLocale, getSupportedLocales, switchLocale, __ } from '@/stores/useOctoLang'

    const locale            = $derived(getLocale())
    const supported_locales = $derived(getSupportedLocales())
</script>

<p>Idioma activo: {locale}</p>

{#each supported_locales as loc (loc)}
    <button
        class={loc === locale ? 'font-bold' : ''}
        onclick={() => switchLocale(loc)}
    >
        {loc.toUpperCase()}
    </button>
{/each}
```

### Integración en `Welcome.svelte`

El instalador detecta tu `Welcome.svelte` y **no lo reemplaza** — solo inyecta los imports y el componente. Así queda una integración típica:

```svelte
<script lang="ts">
    // octolang:start
    import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.svelte'
    import { __ } from '@/stores/useOctoLang'
    // octolang:end
    import { Link, page } from '@inertiajs/svelte'
    import AppHead from '@/components/AppHead.svelte'

    let { canRegister = true }: { canRegister: boolean } = $props()
    const auth = $derived(page.props.auth)
</script>

<AppHead title="Welcome" />

<div class="flex min-h-screen flex-col items-center ...">
    <header class="...">
        <nav class="flex items-center justify-end gap-4">
            <!-- octolang:start -->
            <OctoLangSwitch />
            <!-- octolang:end -->
            {#if auth.user}
                <Link href="/dashboard">Dashboard</Link>
            {:else}
                <Link href="/login">Log in</Link>
                {#if canRegister}
                    <Link href="/register">Register</Link>
                {/if}
            {/if}
        </nav>
    </header>

    <!-- Bloque de mensajes traducidos -->
    <!-- octolang:block:start -->
    <div class="w-full max-w-[335px] text-center mb-6 lg:max-w-4xl">
        <p class="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
            {__('messages.welcome.octolang_thanks')}
        </p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
            {__('messages.welcome.octolang_status')}
        </p>
    </div>
    <!-- octolang:block:end -->

    <!-- resto de tu welcome -->
</div>
```

### Añadir tus propias traducciones

Crea o edita `lang/es/messages.php` y `lang/en/messages.php` en la raíz de tu proyecto:

```php
// lang/es/messages.php
return [
    'nav' => [
        'home'      => 'Inicio',
        'dashboard' => 'Panel',
        'logout'    => 'Cerrar sesión',
    ],
    'hero' => [
        'title'    => 'Bienvenido a mi app',
        'subtitle' => 'La mejor plataforma del mercado',
    ],
];
```

```php
// lang/en/messages.php
return [
    'nav' => [
        'home'      => 'Home',
        'dashboard' => 'Dashboard',
        'logout'    => 'Log out',
    ],
    'hero' => [
        'title'    => 'Welcome to my app',
        'subtitle' => 'The best platform on the market',
    ],
];
```

Úsalas en Svelte:

```svelte
<script lang="ts">
    import { __ } from '@/stores/useOctoLang'
</script>

<h1>{__('messages.hero.title')}</h1>
<a href="/dashboard">{__('messages.nav.dashboard')}</a>
```

## Ejemplos de navbar y sidebar

Ejemplo de archivo de traducciones:

```php
return [
    'home' => 'Inicio',
    'dashboard' => 'Panel',
    'reports' => 'Reportes',
    'settings' => 'Configuración',
    'logout' => 'Cerrar sesión',
];
```

Navbar:

```blade
<nav class="flex items-center gap-4">
    <a href="/">{{ __('messages.home') }}</a>
    <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
    <a href="{{ route('reports.index') }}">{{ __('messages.reports') }}</a>
    <x-octolang::locale-switcher />
</nav>
```

Sidebar:

```blade
<aside class="space-y-3">
    <h2>{{ __('messages.dashboard') }}</h2>

    <ul class="space-y-2">
        <li><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li><a href="{{ route('reports.index') }}">{{ __('messages.reports') }}</a></li>
        <li><a href="{{ route('settings') }}">{{ __('messages.settings') }}</a></li>
    </ul>
</aside>
```

## Ruta disponible

OctoLang registra una ruta para cambiar el idioma:

```php
POST /locale
```

La URI y el nombre real de la ruta salen de la configuración:

- `locale.route_uri`
- `locale.route_name`
- `locale.route_middleware`

Ejemplo de formulario:

```blade
<form method="POST" action="{{ route('locale.store') }}">
    @csrf
    <input type="hidden" name="locale" value="en">
    <button type="submit">English</button>
</form>
```

## Recursos publicables

Si quieres personalizar recursos manualmente, puedes publicarlos:

```bash
php artisan vendor:publish --tag=octolang-config
php artisan vendor:publish --tag=octolang-lang
php artisan vendor:publish --tag=octolang-views
php artisan vendor:publish --tag=octolang-css
```

## Cómo funciona la desinstalación segura

OctoLang registra lo que crea en:

```text
storage/app/octolang/install.json
```

Durante la desinstalación, elimina solo archivos y cambios registrados.

Ejemplos:

- elimina `resources/css/octolang.css` si OctoLang lo creó
- elimina el import en `resources/css/app.css` si OctoLang lo insertó
- elimina archivos dentro de `resources/views/vendor/octolang/...` si fueron creados por OctoLang
- elimina el bloque de OctoLang en el `welcome` solo si puede identificarlo con seguridad
- conserva archivos que ya existían antes de instalar el paquete
- deja archivos modificados para revisión manual si fueron cambiados después

## Flujo recomendado completo

### Instalar

```bash
composer require johannhsdev/octolang
php artisan octolang:install
php artisan optimize:clear
npm run dev
```

### Revisar recursos registrados

```bash
php artisan octolang:status
```

### Desinstalar

```bash
php artisan octolang:uninstall
composer remove johannhsdev/octolang
```

## Testing

```bash
vendor/bin/pest
```

## Contribuciones

Si quieres contribuir a OctoLang, puedes abrir un issue, proponer mejoras o enviar un pull request.

Desarrollo principal:

- [JohannHSDev](https://github.com/johannhsdev)

Dirección visual y apoyo gráfico:

- [Jose Espinoza](https://www.instagram.com/Joeesp25)

## Licencia

MIT

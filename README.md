# OctoLang 🐙

OctoLang is a Laravel package for managing language switching in a simple, explicit way — with a ready-to-use visual switcher and a safe uninstall process.

It is primarily designed for Blade projects, but also works with Livewire, Vue, React, and Svelte when Laravel remains the source of truth for the active locale.

## What the package does

OctoLang includes:

- A `LocaleManager` service to store and resolve the active locale from the session
- A `SetLocale` middleware that applies the active language on every request
- A `POST` route for safely switching languages
- A Blade component: `<x-octolang::locale-switcher />`
- Install, status, and uninstall Artisan commands
- An internal manifest to remove only what OctoLang created or modified

During installation, OctoLang may create or update the following resources depending on the detected stack:

| Resource | Blade | Livewire | Vue | React | Svelte |
|----------|:-----:|:--------:|:---:|:-----:|:------:|
| `resources/views/welcome.blade.php` (injection) | ✓ | ✓ | — | — | — |
| `resources/css/octolang.css` | ✓ | ✓ | ✓ | ✓ | ✓ |
| `resources/css/app.css` (`@import`) | ✓ | ✓ | ✓ | ✓ | ✓ |
| `resources/views/vendor/octolang/components/locale-switcher.blade.php` | ✓ | — | — | — | — |
| `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` | — | ✓ | — | — | — |
| `resources/js/components/octolang/OctoLangSwitch.vue` + `useOctoLang.ts` | — | — | ✓ | — | — |
| `resources/js/components/octolang/LocaleSwitcher.tsx` + `useOctoLang.ts` | — | — | — | ✓ | — |
| `resources/js/components/octolang/OctoLangSwitch.svelte` | — | — | — | — | ✓ |
| `resources/js/stores/useOctoLang.ts` | — | — | — | — | ✓ |
| `lang/en/messages.php` and `lang/es/messages.php` | ✓ | ✓ | ✓ | ✓ | ✓ |
| `storage/app/octolang/install.json` | ✓ | ✓ | ✓ | ✓ | ✓ |

## Requirements

- PHP `^8.2|^8.4`
- Laravel `^11.0|^12.0|^13.0`

## Installation

### 1. Install via Composer

```bash
composer require johannhsdev/octolang
```

### 2. Run the OctoLang installer

After `composer require`, run:

```bash
php artisan octolang:install
php artisan optimize:clear
```

If your project uses Vite, rebuild the assets:

```bash
npm run dev
```

or:

```bash
npm run build
```

## What `php artisan octolang:install` does

The install command runs the package setup explicitly. It does not run automatically on every request.

This command:

1. Copies `lang/en/messages.php` and `lang/es/messages.php` only if those files do not exist
2. Copies `resources/views/vendor/octolang/components/locale-switcher.blade.php` if needed
3. Copies `resources/css/octolang.css` if needed
4. Adds `@import "./octolang.css";` to `resources/css/app.css` if the file exists and does not already contain that line
5. Processes `resources/views/welcome.blade.php`
6. Records created files and tracked changes in `storage/app/octolang/install.json`

## Available commands

### `php artisan octolang:install`

Use it after installing the package with Composer.

When to use:

- when installing OctoLang for the first time
- when you want to regenerate missing package resources
- when you want OctoLang to process the `welcome` view

### `php artisan octolang:status`

Shows the files and mutations that OctoLang has on record.

When to use:

- when you want to audit what OctoLang created
- when you want to know what `octolang:uninstall` will be able to remove

### `php artisan octolang:uninstall`

Removes only the files and changes that OctoLang has on record.

When to use:

- when you want to tear down the package before running `composer remove`
- when you are testing the install/uninstall flow

Recommended uninstall flow:

```bash
php artisan octolang:uninstall
composer remove johannhsdev/octolang
```

## Configuration

To edit the configuration manually, publish it with:

```bash
php artisan vendor:publish --tag=octolang-config
```

Default configuration:

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

### `.env` example

```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOCALE_SUPPORTED=en,es,fr,de
LOCALE_SESSION_KEY=locale
```

Important notes:

- `APP_LOCALE` is the source of truth for OctoLang's initial language.
- `LOCALE_SESSION_KEY` only changes the key used in `session()`.
- `APP_FALLBACK_LOCALE` remains Laravel's responsibility for missing translations.
- `APP_FAKER_LOCALE` only affects Faker and does not participate in the app's visible language.
- `LOCALE_SUPPORTED` validates changes made from the switcher, but does not replace or reorder the base `APP_LOCALE`.

## Supported languages

OctoLang validates language changes against `locale.supported`.

To add more languages, update your config or `.env`:

```env
LOCALE_SUPPORTED=en,es,fr,pt,de,it
```

Then create your translation files:

```text
lang/
  en/
    messages.php
  es/
    messages.php
  fr/
    messages.php
```

Example:

```php
// lang/en/messages.php
return [
    'welcome'   => 'Welcome',
    'dashboard' => 'Dashboard',
    'settings'  => 'Settings',
];
```

```php
// lang/es/messages.php
return [
    'welcome'   => 'Bienvenido',
    'dashboard' => 'Panel',
    'settings'  => 'Configuración',
];
```

## How language switching works

OctoLang saves the selected language in the session and applies it via middleware.

Flow:

1. The user sends a locale to the OctoLang route
2. `LocaleController` validates the locale against `locale.supported`
3. `LocaleManager` saves it in the session
4. `SetLocale` calls `App::setLocale(...)` on the next request, using `APP_LOCALE` as the base
5. Laravel resolves translations using the active locale

When the user switches to a language other than the default, OctoLang pins it in the session.
When they switch back to the default, OctoLang clears that session key so the app follows `APP_LOCALE` again.

In the visual switcher, the active language is always shown as active in both cases.
When the active locale comes from the app default rather than a saved preference, OctoLang adds a subtle visual indicator to signal that the language is following the base configuration.

## Usage in Blade

### Adding the switcher

You can place the component in any Blade view:

```blade
<x-octolang::locale-switcher />
```

Example in a navbar:

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

Example in a sidebar:

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

### Using translations in Blade views

Use Laravel's standard translation helpers:

```blade
<h1>{{ __('messages.welcome') }}</h1>
<p>{{ __('messages.dashboard_description') }}</p>
```

With replacements:

```blade
<p>{{ __('messages.greeting', ['name' => $user->name]) }}</p>
```

Pluralization:

```blade
<p>{{ trans_choice('messages.notifications', $count, ['count' => $count]) }}</p>
```

### Package translation keys

OctoLang also exposes namespaced keys:

```blade
{{ __('octolang::messages.switcher.label') }}
{{ __('octolang::messages.switcher.tooltip') }}
```

In your regular views, prefer your own application keys like `__('messages.welcome')`.

## Usage in Livewire

OctoLang includes a native Livewire v3 component: `<livewire:octolang-switch />`.

It registers automatically when Livewire is installed. No extra backend setup required.

### What `octolang:install` sets up in a Livewire project

When Livewire is detected, the command creates:

- `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` — publishable component view
- `resources/css/octolang.css` and the import in `app.css`
- Injects `<livewire:octolang-switch />` into `welcome.blade.php` without replacing its content

> In Livewire projects, `components/locale-switcher.blade.php` is **not** copied — that file belongs to the pure Blade stack only.

### Adding the switcher

Place the component in any Blade view within your Livewire app:

```blade
<livewire:octolang-switch />
```

Example in a layout:

```blade
<header class="flex items-center justify-between px-6 py-4">
    <a href="/">{{ config('app.name') }}</a>

    <nav class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
        <livewire:octolang-switch />
    </nav>
</header>
```

### How language switching works

1. The user clicks a button in the switcher
2. Livewire calls the `switchLocale($locale)` action via AJAX
3. `LocaleManager::set()` saves the new locale in the session
4. The component dispatches the `octolang:locale-changed` event
5. The component's script listens for that event and calls `window.location.reload()`
6. The browser reloads the page — the `SetLocale` middleware applies the new locale to the entire view

This flow ensures all `__()` calls in the welcome view and any other view are re-rendered with the correct language.

### Using translations

Translations work the same as in pure Blade:

```blade
<h1>{{ __('messages.welcome') }}</h1>
<p>{{ __('messages.dashboard_description') }}</p>
```

With package keys:

```blade
{{ __('octolang::messages.switcher.label') }}
{{ __('octolang::messages.switcher.tooltip') }}
```

### Customizing the component view

If you need to adjust the HTML or styles of the switcher, publish the view:

```bash
php artisan vendor:publish --tag=octolang-views
```

This copies the view to `resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php` where you can edit it freely without touching the package.

## Usage in Vue (Inertia.js)

OctoLang includes native support for Vue 3 with Inertia.js. The `SetLocale` middleware automatically shares the active locale and translations as Inertia props — no backend changes needed beyond installing the package.

### What `octolang:install` sets up in a Vue project

When a Vue/Inertia project is detected, the command creates:

- `resources/js/components/octolang/OctoLangSwitch.vue` — ready-to-use component
- `resources/js/composables/useOctoLang.ts` — composable for accessing translations and switching locale
- `resources/css/octolang.css` and the import in `app.css`

Generated files include the `// octolang:processed` marker so reinstalls do not overwrite them.

### Automatically shared props

The middleware injects the following into every Inertia response:

| Prop | Type | Description |
|------|------|-------------|
| `locale` | `string` | Active locale (`"en"`, `"es"`, etc.) |
| `supported_locales` | `string[]` | Locales enabled in config |
| `translations` | `object` | Translation files from the `octolang::` namespace |

### The `useOctoLang` composable

The composable generated at `resources/js/composables/useOctoLang.ts` exposes:

```ts
const { locale, supported_locales, __, switchLocale } = useOctoLang()
```

- `locale` — reactive computed with the active locale
- `supported_locales` — reactive computed with available locales
- `__('messages.welcome.title')` — translation function equivalent to `__('octolang::messages.welcome.title')` in Blade
- `switchLocale('en')` — sends `POST /locale` and reloads the page via Inertia

### Using translations in a component

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

The key follows the `file.group.key` format. The first segment is the PHP filename inside `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 file      group   key
```

If the first segment does not match any known file, `useOctoLang` defaults to `messages`:

```ts
__('welcome.title')  // equivalent to __('messages.welcome.title')
```

### The `OctoLangSwitch` component

The component generated at `resources/js/components/octolang/OctoLangSwitch.vue` renders a `<nav>` with one button per supported locale. It takes no props — data comes automatically from the composable.

```vue
<script setup lang="ts">
import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'
</script>

<template>
    <nav class="flex items-center gap-4">
        <OctoLangSwitch />
        <!-- rest of your nav -->
    </nav>
</template>
```

Clicking a button sends `POST /locale` and Inertia reloads the page with the updated translations reactively.

### Integration in `welcome.vue`

The installer detects your `welcome.vue` and **does not replace it** — it only injects the import, the composable, and the component. A typical result looks like this:

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

        <!-- rest of your welcome -->
    </div>
</template>
```

### Adding your own translations

Create or edit `lang/en/messages.php` and `lang/es/messages.php` in your project root:

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

Use them in Vue:

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

## Usage in React (Inertia.js)

OctoLang includes native support for React with Inertia.js. The `SetLocale` middleware automatically shares the active locale and translations as Inertia props — no backend changes needed beyond installing the package.

### What `octolang:install` sets up in a React project

When a React/Inertia project is detected, the command creates:

- `resources/js/components/octolang/LocaleSwitcher.tsx` — ready-to-use component
- `resources/js/hooks/useOctoLang.ts` — hook for accessing translations and switching locale
- `resources/css/octolang.css` and the import in `app.css`

Generated files include the `// octolang:processed` marker so reinstalls do not overwrite them.

### Automatically shared props

The middleware injects the following into every Inertia response:

| Prop | Type | Description |
|------|------|-------------|
| `locale` | `string` | Active locale (`"en"`, `"es"`, etc.) |
| `supported_locales` | `string[]` | Locales enabled in config |
| `translations` | `object` | Translation files from the `octolang::` namespace |

### The `useOctoLang` hook

The hook generated at `resources/js/hooks/useOctoLang.ts` exposes:

```ts
const { locale, supported_locales, __, switchLocale } = useOctoLang()
```

- `locale` — string with the active locale
- `supported_locales` — array of available locales
- `__('messages.welcome.title')` — translation function equivalent to `__('octolang::messages.welcome.title')` in Blade
- `switchLocale('en')` — sends `POST /locale` and reloads the page via Inertia

### Using translations in a component

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

The key follows the `file.group.key` format. The first segment is the PHP filename inside `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 file      group   key
```

If the first segment does not match any known file, `useOctoLang` defaults to `messages`:

```ts
__('welcome.title')  // equivalent to __('messages.welcome.title')
```

### The `LocaleSwitcher` component

The component generated at `resources/js/components/octolang/LocaleSwitcher.tsx` renders a `<nav>` with one button per supported locale. It takes no props — data comes automatically from the hook.

```tsx
import LocaleSwitcher from '@/components/octolang/LocaleSwitcher'

<nav className="flex items-center gap-4">
    <LocaleSwitcher />
    {/* rest of your nav */}
</nav>
```

Clicking a button sends `POST /locale` and Inertia reloads the page with the updated translations.

### Integration in `welcome.tsx`

The installer detects your `welcome.tsx` and **does not replace it** — it only injects the import and the hook. A typical result looks like this:

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

                <div className="w-full max-w-4xl text-center mb-6">
                    <p className="text-base font-medium">
                        {__('messages.welcome.octolang_thanks')}
                    </p>
                    <p className="text-sm text-[#706f6c]">
                        {__('messages.welcome.octolang_status')}
                    </p>
                </div>

                {/* rest of your welcome */}
            </div>
        </>
    )
}
```

### Adding your own translations

Create or edit `lang/en/messages.php` and `lang/es/messages.php` in your project root:

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

Use them in React:

```tsx
const { __ } = useOctoLang()

<h1>{__('messages.hero.title')}</h1>
<a href="/dashboard">{__('messages.nav.dashboard')}</a>
```

### Note on Windows

On Windows, OctoLang internally uses `dirname(__DIR__, 3)` and `str_replace('\\', '/', $dir)` to resolve translation file paths with `glob()`. This is transparent to the user — mentioned here only as a compatibility reference.

## Usage in Svelte (Inertia.js)

OctoLang includes native support for Svelte 5 with Inertia.js. The `SetLocale` middleware automatically shares the active locale and translations as Inertia props — no backend changes needed beyond installing the package.

### What `octolang:install` sets up in a Svelte project

When a Svelte/Inertia project is detected, the command creates:

- `resources/js/components/octolang/OctoLangSwitch.svelte` — ready-to-use component
- `resources/js/stores/useOctoLang.ts` — store with access to translations and locale switching
- `resources/css/octolang.css` and the import in `app.css`

Generated files include the `// octolang:processed` marker so reinstalls do not overwrite them.

### Automatically shared props

The middleware injects the following into every Inertia response:

| Prop | Type | Description |
|------|------|-------------|
| `locale` | `string` | Active locale (`"en"`, `"es"`, etc.) |
| `supported_locales` | `string[]` | Locales enabled in config |
| `translations` | `object` | Translation files from the `octolang::` namespace |

### The `useOctoLang` store

The store generated at `resources/js/stores/useOctoLang.ts` exposes:

```ts
import { getLocale, getSupportedLocales, __, switchLocale } from '@/stores/useOctoLang'
```

- `getLocale()` — returns the active locale, reactive via `$derived` in components
- `getSupportedLocales()` — returns the array of available locales
- `__('messages.welcome.title')` — translation function equivalent to `__('octolang::messages.welcome.title')` in Blade
- `switchLocale('en')` — sends `POST /locale` and reloads the page with the new language

> **Svelte 5 note:** `page` from `@inertiajs/svelte` is a `$state` rune object, not a `svelte/store`. That is why the OctoLang store exposes getters instead of `derived()` values. Always wrap them in `$derived()` inside your components to maintain reactivity.

### Using translations in a component

```svelte
<script lang="ts">
    import { __ } from '@/stores/useOctoLang'
</script>

<section>
    <h1>{__('messages.welcome.title')}</h1>
    <p>{__('messages.welcome.octolang_status')}</p>
</section>
```

The key follows the `file.group.key` format. The first segment is the PHP filename inside `lang/{locale}/`:

```
__('messages.welcome.title')
 ↑         ↑       ↑
 file      group   key
```

If the first segment does not match any known file, `useOctoLang` defaults to `messages`:

```ts
__('welcome.title')  // equivalent to __('messages.welcome.title')
```

### The `OctoLangSwitch` component

The component generated at `resources/js/components/octolang/OctoLangSwitch.svelte` renders a `<nav>` with one button per supported locale. It handles its own reactivity internally — no props needed.

```svelte
<script lang="ts">
    import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.svelte'
</script>

<nav class="flex items-center gap-4">
    <OctoLangSwitch />
    <!-- rest of your nav -->
</nav>
```

Clicking a button sends `POST /locale` and the page reloads with the updated translations.

### Reactive access to locale in your components

When you need to react to the active locale (for example for conditional logic), use `$derived` with the getter:

```svelte
<script lang="ts">
    import { getLocale, getSupportedLocales, switchLocale, __ } from '@/stores/useOctoLang'

    const locale            = $derived(getLocale())
    const supported_locales = $derived(getSupportedLocales())
</script>

<p>Active language: {locale}</p>

{#each supported_locales as loc (loc)}
    <button
        class={loc === locale ? 'font-bold' : ''}
        onclick={() => switchLocale(loc)}
    >
        {loc.toUpperCase()}
    </button>
{/each}
```

### Integration in `Welcome.svelte`

The installer detects your `Welcome.svelte` and **does not replace it** — it only injects the imports and the component. A typical result looks like this:

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

    <!-- rest of your welcome -->
</div>
```

### Adding your own translations

Create or edit `lang/en/messages.php` and `lang/es/messages.php` in your project root:

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

Use them in Svelte:

```svelte
<script lang="ts">
    import { __ } from '@/stores/useOctoLang'
</script>

<h1>{__('messages.hero.title')}</h1>
<a href="/dashboard">{__('messages.nav.dashboard')}</a>
```

## Navbar and sidebar examples

Example translation file:

```php
return [
    'home'      => 'Home',
    'dashboard' => 'Dashboard',
    'reports'   => 'Reports',
    'settings'  => 'Settings',
    'logout'    => 'Log out',
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

## Available route

OctoLang registers one route for switching the language:

```
POST /locale
```

The URI and route name come from the configuration:

- `locale.route_uri`
- `locale.route_name`
- `locale.route_middleware`

Example form:

```blade
<form method="POST" action="{{ route('locale.store') }}">
    @csrf
    <input type="hidden" name="locale" value="en">
    <button type="submit">English</button>
</form>
```

## Publishable resources

To customize resources manually, publish them:

```bash
php artisan vendor:publish --tag=octolang-config
php artisan vendor:publish --tag=octolang-lang
php artisan vendor:publish --tag=octolang-views
php artisan vendor:publish --tag=octolang-css
```

## How safe uninstall works

OctoLang tracks everything it creates in:

```
storage/app/octolang/install.json
```

During uninstall, it removes only the files and changes it has on record.

Examples:

- removes `resources/css/octolang.css` if OctoLang created it
- removes the import from `resources/css/app.css` if OctoLang inserted it
- removes files inside `resources/views/vendor/octolang/...` if they were created by OctoLang
- removes the OctoLang block from the `welcome` view only if it can identify it safely
- preserves files that existed before installing the package
- flags files modified after install for manual review

## Recommended full workflow

### Install

```bash
composer require johannhsdev/octolang
php artisan octolang:install
php artisan optimize:clear
npm run dev
```

### Review tracked resources

```bash
php artisan octolang:status
```

### Uninstall

```bash
php artisan octolang:uninstall
composer remove johannhsdev/octolang
```

## Testing

```bash
vendor/bin/pest
```

## Contributing

If you would like to contribute to OctoLang, feel free to open an issue, suggest improvements, or submit a pull request.

Lead development:

- [JohannHSDev](https://github.com/johannhsdev)

Visual direction and graphic support:

- [Jose Espinoza](https://www.instagram.com/Joeesp25)

## License

MIT

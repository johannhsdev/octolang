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

it('uses app locale as the default locale', function () {
    config()->set('app.locale', 'en');

    $manager = app(LocaleManager::class);

    expect($manager->default())->toBe('en')
        ->and($manager->current())->toBe('en');
});

it('falls back to en when app locale is empty', function () {
    config()->set('app.locale', null);

    $manager = app(LocaleManager::class);

    expect($manager->default())->toBe('en');
});

it('uses the configured session key instead of a hard-coded one', function () {
    config()->set('locale.session_key', 'frontend_locale');

    $manager = app(LocaleManager::class);
    $manager->set('fr');

    expect(session('frontend_locale'))->toBe('fr')
        ->and(session()->has('locale'))->toBeFalse()
        ->and($manager->current())->toBe('fr');
});

it('respects app locale even when it is not listed in supported locales', function () {
    config()->set('app.locale', 'pt');
    config()->set('locale.supported', ['es', 'en', 'fr']);

    $manager = app(LocaleManager::class);

    expect($manager->default())->toBe('pt')
        ->and($manager->current())->toBe('pt');
});

it('clears the session when setting the default locale', function () {
    $manager = app(LocaleManager::class);

    $manager->set('fr');
    expect(session('locale'))->toBe('fr');

    $manager->set('es');

    expect(session()->has('locale'))->toBeFalse()
        ->and($manager->current())->toBe('es');
});

it('reports whether the current locale comes from session or default resolution', function () {
    $manager = app(LocaleManager::class);

    expect($manager->currentSource())->toBe('default');

    $manager->set('fr');

    expect($manager->currentSource())->toBe('session');

    $manager->set('es');

    expect($manager->currentSource())->toBe('default');
});

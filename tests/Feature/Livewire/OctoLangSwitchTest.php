<?php

use Johannhsdev\OctoLang\Livewire\OctoLangSwitch;
use Johannhsdev\OctoLang\LocaleManager;
use Livewire\LivewireManager;

beforeEach(function () {
    if (! class_exists(\Livewire\Livewire::class)) {
        test()->markTestSkipped('livewire/livewire is not installed.');
    }
});

it('mounts with the current locale from the manager', function () {
    $this->app['config']->set('locale.supported', ['en', 'es']);
    $this->app['config']->set('app.locale', 'en');

    \Livewire\Livewire::test(OctoLangSwitch::class)
        ->assertSet('locale', 'en');
});

it('switchLocale updates the locale property and the session', function () {
    $this->app['config']->set('locale.supported', ['en', 'es']);
    $this->app['config']->set('app.locale', 'en');

    \Livewire\Livewire::test(OctoLangSwitch::class)
        ->call('switchLocale', 'es')
        ->assertSet('locale', 'es');

    $manager = app(LocaleManager::class);
    expect($manager->current())->toBe('es');
});

it('ignores unsupported locales and keeps the current locale', function () {
    $this->app['config']->set('locale.supported', ['en', 'es']);
    $this->app['config']->set('app.locale', 'en');

    \Livewire\Livewire::test(OctoLangSwitch::class)
        ->call('switchLocale', 'fr')
        ->assertSet('locale', 'en');
});

it('clears the session override when switching to the default locale', function () {
    $this->app['config']->set('locale.supported', ['en', 'es']);
    $this->app['config']->set('app.locale', 'en');

    $manager = app(LocaleManager::class);
    $manager->set('es');

    \Livewire\Livewire::test(OctoLangSwitch::class)
        ->assertSet('locale', 'es')
        ->call('switchLocale', 'en')
        ->assertSet('locale', 'en');

    expect($manager->isUsingSessionOverride())->toBeFalse();
});

it('renders without errors', function () {
    $this->app['config']->set('locale.supported', ['en', 'es']);
    $this->app['config']->set('app.locale', 'en');

    \Livewire\Livewire::test(OctoLangSwitch::class)
        ->assertStatus(200);
});

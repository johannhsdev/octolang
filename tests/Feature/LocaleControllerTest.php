<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Johannhsdev\OctoLang\Setup\InstallManifest;
use Johannhsdev\OctoLang\Http\Middleware\SetLocale;

beforeEach(function () {
    Route::middleware(['web', SetLocale::class])
        ->get('/__test_locale', fn () => response(app()->getLocale()));

    Route::middleware(['web', SetLocale::class])
        ->get('/__test_switcher', fn () => view('test-switcher'));
});

it('stores a valid locale and redirects back', function () {
    $this->post('/locale', ['locale' => 'en'])
        ->assertRedirect();

    expect(session('locale'))->toBe('en');
});

it('rejects an invalid locale with validation error', function () {
    $this->post('/locale', ['locale' => 'jp'])
        ->assertSessionHasErrors('locale');
});

it('applies the default locale via middleware when no session exists', function () {
    $response = $this->get('/__test_locale');

    $response->assertOk();
    expect($response->getContent())->toBe('es');
});

it('applies the session locale via middleware', function () {
    session(['locale' => 'fr']);

    $response = $this->get('/__test_locale');

    $response->assertOk();
    expect($response->getContent())->toBe('fr');
});

it('falls back to default when session has unsupported locale', function () {
    session(['locale' => 'jp']);

    $response = $this->get('/__test_locale');

    $response->assertOk();
    expect($response->getContent())->toBe('es');
});

it('renders the locale switcher component', function () {
    $response = $this->get('/__test_switcher');

    $response->assertOk();
    $response->assertSee('octolang-switcher', escape: false);
    $response->assertSee('data-source="default"', escape: false);
    $response->assertSee('octolang-status-dot', escape: false);
});

it('does not auto-install tracked files on boot', function () {
    expect(app(InstallManifest::class)->exists())->toBeFalse();
});

it('applies the app locale when no session exists', function () {
    config()->set('app.locale', 'en');

    $response = $this->get('/__test_locale');

    $response->assertOk();
    expect($response->getContent())->toBe('en');
});

it('clears the session when switching back to the default locale', function () {
    config()->set('app.locale', 'en');
    session(['locale' => 'fr']);

    $this->from('/__test_locale')
        ->post('/locale', ['locale' => 'en'])
        ->assertRedirect('/__test_locale');

    expect(session()->has('locale'))->toBeFalse();

    config()->set('app.locale', 'es');

    $response = $this->get('/__test_locale');

    $response->assertOk();
    expect($response->getContent())->toBe('es');
});

it('shares app translation files alongside package translations for inertia consumption', function () {
    $langPath = app()->langPath();

    File::ensureDirectoryExists($langPath.'/es');
    File::ensureDirectoryExists($langPath.'/en');

    File::put($langPath.'/es/greet.php', <<<'PHP'
<?php

return [
    'greeting' => [
        'morning' => '¡Buenos días!',
    ],
];
PHP);

    File::put($langPath.'/en/greet.php', <<<'PHP'
<?php

return [
    'greeting' => [
        'morning' => 'Good morning!',
    ],
];
PHP);

    File::put($langPath.'/es/messages.php', <<<'PHP'
<?php

return [
    'welcome' => [
        'custom_line' => 'Texto app',
    ],
];
PHP);

    session(['locale' => 'es']);

    /** @var \Johannhsdev\OctoLang\Http\Middleware\SetLocale $middleware */
    $middleware = app(SetLocale::class);
    $translations = $middleware->getTranslations('es');

    expect($translations['greet']['greeting']['morning'])->toBe('¡Buenos días!')
        ->and($translations['messages']['welcome']['custom_line'])->toBe('Texto app')
        ->and($translations['messages']['welcome']['octolang_thanks'])->toBeString();

    File::delete($langPath.'/es/greet.php');
    File::delete($langPath.'/en/greet.php');
    File::delete($langPath.'/es/messages.php');
});

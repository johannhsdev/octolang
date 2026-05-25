<?php

use Johannhsdev\OctoLang\Setup\Injectors\BladeInjector;
use Johannhsdev\OctoLang\Setup\Injectors\LivewireInjector;
use Johannhsdev\OctoLang\Setup\Injectors\ReactInjector;
use Johannhsdev\OctoLang\Setup\Injectors\SvelteInjector;
use Johannhsdev\OctoLang\Setup\Injectors\VueInjector;
use Johannhsdev\OctoLang\Setup\InstallManifest;
use Johannhsdev\OctoLang\Setup\OctoLangInstaller;
use Johannhsdev\OctoLang\Setup\StackDetector;
use Johannhsdev\OctoLang\Setup\WelcomeHandler;

function makeInstallerBase(array $files): string
{
    $base = sys_get_temp_dir().'/octolang-installer-test-'.uniqid();
    mkdir($base, 0755, true);

    foreach ($files as $relative => $content) {
        $full = $base.'/'.$relative;

        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0755, true);
        }

        file_put_contents($full, $content);
    }

    return $base;
}

it('installs tracked resources only when the installer runs', function () {
    $base = makeInstallerBase([
        'resources/views/welcome.blade.php' => '<html><body>Custom welcome</body></html>',
        'resources/css/app.css' => 'body { color: red; }',
    ]);

    $stubsDir = dirname(__DIR__, 3).'/resources/stubs';
    $manifest = new InstallManifest($base);
    $detector = new StackDetector($base);
    $handler  = new WelcomeHandler(
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

    $installer = new OctoLangInstaller($base, $manifest, $handler);
    $installer->synchronize();
    $installer->handleWelcomeView();

    expect($base.'/resources/css/octolang.css')->toBeFile();
    expect($base.'/resources/views/vendor/octolang/components/locale-switcher.blade.php')->toBeFile();
    expect(file_get_contents($base.'/resources/css/app.css'))->toContain('@import "./octolang.css";');
    expect(file_get_contents($base.'/resources/views/welcome.blade.php'))->toContain(WelcomeHandler::BLOCK_START);
    expect($manifest->exists())->toBeTrue();
});

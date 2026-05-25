<?php

use Johannhsdev\OctoLang\Setup\InstallManifest;
use Johannhsdev\OctoLang\Setup\UninstallManager;
use Johannhsdev\OctoLang\Setup\WelcomeHandler;

function makeUninstallBase(array $files): string
{
    $base = sys_get_temp_dir().'/octolang-uninstall-test-'.uniqid();
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

it('removes tracked files and mutations', function () {
    $base = makeUninstallBase([
        'resources/css/app.css' => '@import "./octolang.css";'.PHP_EOL.'body { color: red; }',
        'resources/css/octolang.css' => '/* octolang */',
        'resources/views/vendor/octolang/components/locale-switcher.blade.php' => '<div>switcher</div>',
        'resources/views/welcome.blade.php' => implode(PHP_EOL, [
            WelcomeHandler::HTML_NOTE_START,
            '<!-- octolang:processed -->',
            WelcomeHandler::HTML_NOTE_END,
            '<body>',
            WelcomeHandler::BLOCK_START,
            '<x-octolang::locale-switcher />',
            WelcomeHandler::BLOCK_END,
            '</body>',
        ]),
    ]);

    $manifest = new InstallManifest($base);
    $manifest->recordCreatedFile('resources/css/octolang.css', 'css');
    $manifest->recordCreatedFile('resources/views/vendor/octolang/components/locale-switcher.blade.php', 'view');
    $manifest->recordMutation('resources/css/app.css', 'octolang_css_import', [
        'type' => 'exact_line',
        'line' => '@import "./octolang.css";',
    ]);
    $manifest->recordMutation('resources/views/welcome.blade.php', 'welcome_html_note', [
        'type' => 'marked_block',
        'start_marker' => WelcomeHandler::HTML_NOTE_START,
        'end_marker' => WelcomeHandler::HTML_NOTE_END,
    ]);
    $manifest->recordMutation('resources/views/welcome.blade.php', 'welcome_blade_block', [
        'type' => 'marked_block',
        'start_marker' => WelcomeHandler::BLOCK_START,
        'end_marker' => WelcomeHandler::BLOCK_END,
    ]);

    $summary = (new UninstallManager($base, $manifest))->uninstall();

    expect($summary['removed'])->toContain('resources/css/octolang.css');
    expect($summary['removed'])->toContain('resources/views/vendor/octolang/components (empty directory)');
    expect($summary['removed'])->toContain('resources/views/vendor/octolang (empty directory)');
    expect($base.'/resources/css/octolang.css')->not->toBeFile();
    expect($base.'/resources/views/vendor/octolang')->not->toBeDirectory();
    expect(file_get_contents($base.'/resources/css/app.css'))->not->toContain('@import "./octolang.css";');
    expect(file_get_contents($base.'/resources/views/welcome.blade.php'))->not->toContain(WelcomeHandler::BLOCK_START);
    expect(file_exists($manifest->path()))->toBeFalse();
});

it('keeps tracked files that were modified after install', function () {
    $base = makeUninstallBase([
        'lang/en/messages.php' => '<?php return ["hello" => "changed"];',
    ]);

    $manifest = new InstallManifest($base);
    $manifest->recordCreatedFile('lang/en/messages.php', 'lang');

    file_put_contents($base.'/lang/en/messages.php', '<?php return ["hello" => "user changed"];');

    $summary = (new UninstallManager($base, $manifest))->uninstall();

    expect($summary['manual'])->toContain('lang/en/messages.php (file changed after install)');
    expect($base.'/lang/en/messages.php')->toBeFile();
});

it('reports obvious leftovers when the manifest is missing', function () {
    $base = makeUninstallBase([
        'resources/css/app.css' => '@import "./octolang.css";',
        'resources/css/octolang.css' => '/* octolang */',
        'resources/views/welcome.blade.php' => '<!-- octolang:processed -->',
    ]);

    $manifest = new InstallManifest($base);
    $summary = (new UninstallManager($base, $manifest))->uninstall();

    expect($summary['removed'])->toBe([]);
    expect($summary['manual'])->toContain('resources/css/app.css');
    expect($summary['manual'])->toContain('resources/css/octolang.css');
    expect($summary['manual'])->toContain('resources/views/welcome.blade.php');
});

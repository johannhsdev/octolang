<?php

use Johannhsdev\OctoLang\Setup\StackDetector;

function makeTempProject(array $files): string
{
    $base = sys_get_temp_dir().'/octolang-test-'.uniqid();
    mkdir($base, 0755, true);

    foreach ($files as $relative) {
        $full = $base.'/'.$relative;
        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0755, true);
        }
        file_put_contents($full, '');
    }

    return $base;
}

afterEach(function () {
    // Cleanup handled by OS temp dir
});

it('detects blade stack', function () {
    $base = makeTempProject(['resources/views/welcome.blade.php']);

    expect((new StackDetector($base))->detect())->toBe('blade');
});

it('detects vue stack', function () {
    $base = makeTempProject([
        'resources/views/welcome.blade.php',
        'resources/js/Pages/Welcome.vue',
    ]);

    expect((new StackDetector($base))->detect())->toBe('vue');
});

it('detects react stack with jsx', function () {
    $base = makeTempProject([
        'resources/views/welcome.blade.php',
        'resources/js/Pages/Welcome.jsx',
    ]);

    expect((new StackDetector($base))->detect())->toBe('react');
});

it('detects react stack with tsx', function () {
    $base = makeTempProject([
        'resources/views/welcome.blade.php',
        'resources/js/Pages/Welcome.tsx',
    ]);

    expect((new StackDetector($base))->detect())->toBe('react');
});

it('returns unknown when no welcome file found', function () {
    $base = makeTempProject([]);

    expect((new StackDetector($base))->detect())->toBe('unknown');
});

it('prefers vue over blade when both exist', function () {
    $base = makeTempProject([
        'resources/views/welcome.blade.php',
        'resources/js/Pages/Welcome.vue',
    ]);

    expect((new StackDetector($base))->detect())->toBe('vue');
});

it('resolves correct path for blade', function () {
    $base = makeTempProject(['resources/views/welcome.blade.php']);

    expect((new StackDetector($base))->resolvePath('blade'))
        ->toBe($base.'/resources/views/welcome.blade.php');
});

it('detects react stack with lowercase pages/welcome.tsx', function () {
    $base = makeTempProject([
        'resources/js/pages/welcome.tsx',
    ]);

    expect((new StackDetector($base))->detect())->toBe('react');
});

it('detects react stack with lowercase pages/welcome.jsx', function () {
    $base = makeTempProject([
        'resources/js/pages/welcome.jsx',
    ]);

    expect((new StackDetector($base))->detect())->toBe('react');
});

it('resolves correct lowercase path for react tsx', function () {
    $base = makeTempProject([
        'resources/js/pages/welcome.tsx',
    ]);

    $resolved = (new StackDetector($base))->resolvePath('react');

    // Path must be non-null and the file must exist.
    // We use file_exists instead of strict string comparison because Windows
    // filesystems are case-insensitive — the path string may differ in casing.
    expect($resolved)->not->toBeNull();
    expect(file_exists($resolved))->toBeTrue();
});

it('prefers capital Pages/Welcome over lowercase when both exist', function () {
    $base = makeTempProject([
        'resources/js/Pages/Welcome.tsx',
        'resources/js/pages/welcome.tsx',
    ]);

    expect((new StackDetector($base))->resolvePath('react'))
        ->toBe($base.'/resources/js/Pages/Welcome.tsx');
});

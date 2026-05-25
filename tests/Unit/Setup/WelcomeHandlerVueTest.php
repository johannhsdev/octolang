<?php

use Johannhsdev\OctoLang\Setup\Injectors\VueInjector;
use Johannhsdev\OctoLang\Setup\StackDetector;
use Johannhsdev\OctoLang\Setup\WelcomeHandler;

function makeVueHandler(string $base): WelcomeHandler
{
    $stubsDir = sys_get_temp_dir().'/octolang-vue-stubs-'.uniqid();
    mkdir($stubsDir, 0755, true);

    file_put_contents($stubsDir.'/welcome.vue', '<template><!-- octolang:processed vue stub --></template>');

    $detector = new StackDetector($base);

    return new WelcomeHandler(
        detector:  $detector,
        injectors: [
            'vue' => new VueInjector($stubsDir, $detector),
        ],
    );
}

function makeVueTempBase(array $files): string
{
    $base = sys_get_temp_dir().'/octolang-vue-handler-test-'.uniqid();
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

it('inyecta import en <script setup> cuando welcome.vue no tiene marcador', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>
<template>
    <div>
        <header><nav class="flex"><a href="/login">Login</a></nav></header>
    </div>
</template>
VUE,
    ]);

    makeVueHandler($base)->handle();

    $result = file_get_contents($base.'/resources/js/Pages/Welcome.vue');

    expect($result)
        ->toContain('octolang:processed')
        ->toContain(WelcomeHandler::SCRIPT_BLOCK_START)
        ->toContain("import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'")
        ->toContain(WelcomeHandler::SCRIPT_BLOCK_END);
});

it('inyecta <OctoLangSwitch /> dentro del <nav> existente', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
</script>
<template>
    <header>
        <nav class="flex items-center justify-end gap-4">
            <a href="/login">Login</a>
        </nav>
    </header>
</template>
VUE,
    ]);

    makeVueHandler($base)->handle();

    $result = file_get_contents($base.'/resources/js/Pages/Welcome.vue');

    expect($result)
        ->toContain(WelcomeHandler::VUE_BLOCK_START)
        ->toContain('<OctoLangSwitch />')
        ->toContain(WelcomeHandler::VUE_BLOCK_END)
        ->toContain('<a href="/login">Login</a>');
});

it('usa <header> como fallback cuando no existe <nav>', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
</script>
<template>
    <header class="w-full">
        <a href="/login">Login</a>
    </header>
</template>
VUE,
    ]);

    makeVueHandler($base)->handle();

    $result = file_get_contents($base.'/resources/js/Pages/Welcome.vue');

    expect($result)
        ->toContain('<OctoLangSwitch />')
        ->toContain('<a href="/login">Login</a>');
});

it('no duplica la inyección cuando handle() se llama dos veces', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
</script>
<template>
    <nav><a href="/">Home</a></nav>
</template>
VUE,
    ]);

    $handler = makeVueHandler($base);
    $handler->handle();
    $handler->handle();

    $result = file_get_contents($base.'/resources/js/Pages/Welcome.vue');

    expect(substr_count($result, 'octolang:processed'))->toBe(1);
    expect(substr_count($result, '<OctoLangSwitch />'))->toBe(1);
});

it('preserva el contenido original del <nav> tras la inyección', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>
<template>
    <nav class="flex gap-4">
        <Link href="/dashboard">Dashboard</Link>
    </nav>
</template>
VUE,
    ]);

    makeVueHandler($base)->handle();

    expect(file_get_contents($base.'/resources/js/Pages/Welcome.vue'))
        ->toContain('<Link href="/dashboard">Dashboard</Link>');
});

it('preserva imports y lógica existente del <script setup> tras la inyección', function () {
    $base = makeVueTempBase([
        'resources/js/Pages/Welcome.vue' => <<<'VUE'
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard } from '@/routes'

withDefaults(defineProps<{ canRegister: boolean }>(), { canRegister: true })
</script>
<template>
    <nav><a href="/">Home</a></nav>
</template>
VUE,
    ]);

    makeVueHandler($base)->handle();

    $result = file_get_contents($base.'/resources/js/Pages/Welcome.vue');

    expect($result)
        ->toContain("import { Head, Link } from '@inertiajs/vue3'")
        ->toContain("import { dashboard } from '@/routes'")
        ->toContain('withDefaults(defineProps');
});

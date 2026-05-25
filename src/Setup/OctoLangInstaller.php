<?php

namespace Johannhsdev\OctoLang\Setup;

class OctoLangInstaller
{
    private const APP_CSS_IMPORT = '@import "./octolang.css";';

    public function __construct(
        private readonly string $basePath,
        private readonly InstallManifest $manifest,
        private readonly StackDetector $detector,
    ) {}

    public function synchronize(): void
    {
        $stack = $this->detector->detect();
        $this->copyLangFilesIfMissing();
        $this->copyViewsIfMissing($stack);
        $this->injectCssIfMissing();
        $this->copyReactComponentsIfMissing($stack);
        $this->copyVueComponentsIfMissing($stack);
        $this->copyLivewireViewsIfMissing();
        $this->copySvelteComponentsIfMissing($stack);
    }

    private function copyLangFilesIfMissing(): void
    {
        $source = __DIR__.'/../../lang';

        foreach (['en', 'es'] as $locale) {
            $relativeTarget = 'lang/'.$locale.'/messages.php';
            $targetFile = $this->basePath.'/'.$relativeTarget;

            if (file_exists($targetFile)) {
                continue;
            }

            $sourceFile = $source.'/'.$locale.'/messages.php';
            $this->ensureParentDirectoryExists($targetFile);
            copy($sourceFile, $targetFile);
            $this->manifest->recordCreatedFile($relativeTarget, 'lang');
        }
    }

    private function copyViewsIfMissing(string $stack): void
    {

        // locale-switcher.blade.php is only used by the Blade form-POST flow.
        // Livewire uses its own octo-lang-switch component — no overlap needed.
        if ($stack === 'livewire') {
            return;
        }

        $source = __DIR__.'/../../resources/views';
        $components = ['components/locale-switcher.blade.php'];

        foreach ($components as $relative) {
            $relativeTarget = 'resources/views/vendor/octolang/'.$relative;
            $targetFile = $this->basePath.'/'.$relativeTarget;

            if (file_exists($targetFile)) {
                continue;
            }

            $sourceFile = $source.'/'.$relative;
            $this->ensureParentDirectoryExists($targetFile);
            copy($sourceFile, $targetFile);
            $this->manifest->recordCreatedFile($relativeTarget, 'view');
        }
    }

    private function injectCssIfMissing(): void
    {
        $cssSource = __DIR__.'/../../resources/css/octolang.css';
        $relativeCssTarget = 'resources/css/octolang.css';
        $cssTarget = $this->basePath.'/'.$relativeCssTarget;
        $appCss = $this->basePath.'/resources/css/app.css';

        if (! file_exists($cssTarget)) {
            $this->ensureParentDirectoryExists($cssTarget);
            copy($cssSource, $cssTarget);
            $this->manifest->recordCreatedFile($relativeCssTarget, 'css');
        }

        if (! file_exists($appCss)) {
            return;
        }

        $contents = (string) file_get_contents($appCss);

        if (str_contains($contents, self::APP_CSS_IMPORT)) {
            return;
        }

        file_put_contents($appCss, self::APP_CSS_IMPORT.PHP_EOL.$contents);

        $this->manifest->recordMutation('resources/css/app.css', 'octolang_css_import', [
            'type' => 'exact_line',
            'line' => self::APP_CSS_IMPORT,
        ]);
    }

    private function copyReactComponentsIfMissing(string $stack): void
    {
        if ($stack !== 'react') {
            return;
        }

        $stubsDir = __DIR__.'/../../resources/stubs';

        $files = [
            'resources/js/components/octolang/LocaleSwitcher.tsx' => $stubsDir.'/LocaleSwitcher.tsx',
            'resources/js/hooks/useOctoLang.ts'                   => $stubsDir.'/useOctoLang.ts',
        ];

        foreach ($files as $relativeTarget => $sourceFile) {
            $targetFile = $this->basePath.'/'.$relativeTarget;

            if (file_exists($targetFile)) {
                continue;
            }

            $this->ensureParentDirectoryExists($targetFile);
            copy($sourceFile, $targetFile);
            $this->manifest->recordCreatedFile($relativeTarget, 'react_component');
        }
    }

    private function copyVueComponentsIfMissing(string $stack): void
    {
        if ($stack !== 'vue') {
            return;
        }

        $stubsDir = __DIR__.'/../../resources/stubs';

        $files = [
            'resources/js/components/octolang/OctoLangSwitch.vue' => $stubsDir.'/OctoLangSwitch.vue',
            'resources/js/composables/useOctoLang.ts'             => $stubsDir.'/useOctoLang.vue.ts',
        ];

        foreach ($files as $relativeTarget => $sourceFile) {
            $targetFile = $this->basePath.'/'.$relativeTarget;

            if (file_exists($targetFile)) {
                continue;
            }

            $this->ensureParentDirectoryExists($targetFile);
            copy($sourceFile, $targetFile);
            $this->manifest->recordCreatedFile($relativeTarget, 'vue_component');
        }
    }

    private function copyLivewireViewsIfMissing(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        $source       = __DIR__.'/../../resources/views/livewire/octo-lang-switch.blade.php';
        $relativeTarget = 'resources/views/vendor/octolang/livewire/octo-lang-switch.blade.php';
        $target       = $this->basePath.'/'.$relativeTarget;

        if (file_exists($target)) {
            return;
        }

        $this->ensureParentDirectoryExists($target);
        copy($source, $target);
        $this->manifest->recordCreatedFile($relativeTarget, 'livewire_view');
    }

    private function copySvelteComponentsIfMissing(string $stack): void
    {
        if ($stack !== 'svelte') {
            return;
        }

        $stubsDir = __DIR__.'/../../resources/stubs';

        $files = [
            'resources/js/components/octolang/OctoLangSwitch.svelte' => $stubsDir.'/OctoLangSwitch.svelte',
            'resources/js/stores/useOctoLang.ts'                     => $stubsDir.'/useOctoLang.svelte.ts',
        ];

        foreach ($files as $relativeTarget => $sourceFile) {
            $targetFile = $this->basePath.'/'.$relativeTarget;

            if (file_exists($targetFile)) {
                continue;
            }

            $this->ensureParentDirectoryExists($targetFile);
            copy($sourceFile, $targetFile);
            $this->manifest->recordCreatedFile($relativeTarget, 'svelte_component');
        }
    }

    private function ensureParentDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}

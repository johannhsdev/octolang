<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

final class VueInjector extends BaseInjector
{
    public function stack(): string
    {
        return 'vue';
    }

    public function inject(string $path, string $original): void
    {
        if ($original === '') {
            $this->overwriteWithStub($path);
            return;
        }

        $importLine = "import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'";

        $modified = $this->injectVueScriptImport($original, $importLine);
        $modified = $this->injectVueUseOctoLang($modified);
        $modified = $this->injectVueNavComponent($modified, '<OctoLangSwitch />');
        $modified = $this->injectVueTranslationBlock($modified);

        file_put_contents($path, $this->vueNote().PHP_EOL.$modified);

        $this->recordMutation($path, 'welcome_vue_import', [
            'type'         => 'marked_block',
            'start_marker' => self::SCRIPT_BLOCK_START,
            'end_marker'   => self::SCRIPT_BLOCK_END,
        ]);

        $this->recordMutation($path, 'welcome_vue_component', [
            'type'         => 'marked_block',
            'start_marker' => self::VUE_BLOCK_START,
            'end_marker'   => self::VUE_BLOCK_END,
        ]);

        $this->recordMutation($path, 'welcome_vue_translation_block', [
            'type'         => 'marked_block',
            'start_marker' => self::BLOCK_START,
            'end_marker'   => self::BLOCK_END,
        ]);
    }

    private function overwriteWithStub(string $path): void
    {
        $this->ensureParentDirectoryExists($path);
        copy($this->stubsDir.'/welcome.vue', $path);
        $this->recordCreatedFile($path, 'welcome');
    }

    private function injectVueScriptImport(string $contents, string $importLine): string
    {
        $block = self::SCRIPT_BLOCK_START.PHP_EOL.$importLine.PHP_EOL.self::SCRIPT_BLOCK_END;

        $modified = preg_replace(
            '/(<script\b[^>]*setup[^>]*>)([\s\S]*?)(import\s)/i',
            '$1$2'.$block.PHP_EOL.'$3',
            $contents,
            limit: 1,
            count: $count,
        );

        if ($count > 0 && $modified !== null) {
            return $modified;
        }

        $modified = preg_replace(
            '/(<script\b[^>]*setup[^>]*>)/i',
            '$1'.PHP_EOL.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function injectVueUseOctoLang(string $contents): string
    {
        $importLine  = "import { useOctoLang } from '@/composables/useOctoLang'";
        $destructure = 'const { __ } = useOctoLang()';

        $block = self::SCRIPT_BLOCK_START.PHP_EOL
               .$importLine.PHP_EOL
               .$destructure.PHP_EOL
               .self::SCRIPT_BLOCK_END;

        $modified = preg_replace(
            '/(<\/script>)/i',
            $block.PHP_EOL.'$1',
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function injectVueNavComponent(string $contents, string $componentTag): string
    {
        $block = PHP_EOL.'                '.self::VUE_BLOCK_START.PHP_EOL
               .'                '.$componentTag.PHP_EOL
               .'                '.self::VUE_BLOCK_END;

        $modified = preg_replace(
            '/(<nav\b[^>]*>)/i',
            '$1'.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        if ($count > 0 && $modified !== null) {
            return $modified;
        }

        $modified = preg_replace(
            '/(<header\b[^>]*>)/i',
            '$1'.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function injectVueTranslationBlock(string $contents): string
    {
        $block = PHP_EOL.$this->vueTranslationBlock();

        $modified = preg_replace(
            '/(<\/header>)/i',
            '$1'.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function vueTranslationBlock(): string
    {
        return <<<'VUE'
            <!-- octolang:block:start -->
            <div class="w-full max-w-[335px] text-center mb-6 lg:max-w-4xl">
                <p class="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                    {{ __('messages.welcome.octolang_thanks') }}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('messages.welcome.octolang_status') }}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('messages.welcome.octolang_hint') }}
                </p>
            </div>
            <!-- octolang:block:end -->
        VUE;
    }

    private function vueNote(): string
    {
        return <<<'VUE'
            <!--
            octolang:processed
            ┌──────────────────────────────────────────────────────────────────────┐
            │  OctoLang — Multi-language Package                                   │
            │  OctoLang inyectó el componente OctoLangSwitch en este archivo.      │
            │                                                                      │
            │  Bloques marcados (reversibles con octolang:uninstall):              │
            │    · <script setup>: import OctoLangSwitch.vue                       │
            │    · <template><nav>: componente OctoLangSwitch                      │
            │                                                                      │
            │  Documentación: https://github.com/johannhsdev/octolang              │
            └──────────────────────────────────────────────────────────────────────┘
            -->
        VUE;
    }
}

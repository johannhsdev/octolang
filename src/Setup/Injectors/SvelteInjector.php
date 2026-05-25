<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

final class SvelteInjector extends BaseInjector
{
    public function stack(): string
    {
        return 'svelte';
    }

    public function inject(string $path, string $original): void
    {
        $importComponent = "import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.svelte'";
        $importStore     = "import { __ } from '@/stores/useOctoLang'";

        $modified = $this->injectSvelteScriptImports($original, $importComponent, $importStore);
        $modified = $this->injectSvelteNavComponent($modified, '<OctoLangSwitch />');
        $modified = $this->injectSvelteTranslationBlock($modified);

        file_put_contents($path, $this->svelteNote().PHP_EOL.$modified);

        $this->recordMutation($path, 'welcome_svelte_import', [
            'type'         => 'marked_block',
            'start_marker' => self::SCRIPT_BLOCK_START,
            'end_marker'   => self::SCRIPT_BLOCK_END,
        ]);

        $this->recordMutation($path, 'welcome_svelte_component', [
            'type'         => 'marked_block',
            'start_marker' => self::VUE_BLOCK_START,
            'end_marker'   => self::VUE_BLOCK_END,
        ]);

        $this->recordMutation($path, 'welcome_svelte_translation_block', [
            'type'         => 'marked_block',
            'start_marker' => self::BLOCK_START,
            'end_marker'   => self::BLOCK_END,
        ]);
    }

    private function injectSvelteScriptImports(string $contents, string ...$imports): string
    {
        $block = self::SCRIPT_BLOCK_START.PHP_EOL
               .implode(PHP_EOL, $imports).PHP_EOL
               .self::SCRIPT_BLOCK_END;

        $modified = preg_replace(
            '/(<script\b[^>]*>)([\s\S]*?)(import\s)/i',
            '$1$2'.$block.PHP_EOL.'$3',
            $contents,
            limit: 1,
            count: $count,
        );

        if ($count > 0 && $modified !== null) {
            return $modified;
        }

        $modified = preg_replace(
            '/(<script\b[^>]*>)/i',
            '$1'.PHP_EOL.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function injectSvelteNavComponent(string $contents, string $componentTag): string
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

    private function injectSvelteTranslationBlock(string $contents): string
    {
        $block = PHP_EOL.$this->svelteTranslationBlock();

        $modified = preg_replace(
            '/(<\/header>)/i',
            '$1'.$block,
            $contents,
            limit: 1,
            count: $count,
        );

        return ($count > 0 && $modified !== null) ? $modified : $contents;
    }

    private function svelteTranslationBlock(): string
    {
        return <<<'SVELTE'
            <!-- octolang:block:start -->
            <div class="w-full max-w-[335px] text-center mb-6 lg:max-w-4xl">
                <p class="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                    {__('messages.welcome.octolang_thanks')}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {__('messages.welcome.octolang_status')}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {__('messages.welcome.octolang_hint')}
                </p>
            </div>
            <!-- octolang:block:end -->
        SVELTE;
    }

    private function svelteNote(): string
    {
        return <<<'SVELTE'
            <!--
            octolang:processed
            ┌──────────────────────────────────────────────────────────────────────┐
            │  OctoLang — Multi-language Package                                   │
            │  OctoLang inyectó el componente OctoLangSwitch en este archivo.      │
            │                                                                      │
            │  Bloques marcados (reversibles con octolang:uninstall):              │
            │    · <script lang="ts">: import OctoLangSwitch + __ de useOctoLang   │
            │    · <nav> / <header>: componente OctoLangSwitch                     │
            │    · bloque de traducciones tras </header>                           │
            │                                                                      │
            │  Documentación: https://github.com/johannhsdev/octolang              │
            └──────────────────────────────────────────────────────────────────────┘
            -->
        SVELTE;
    }
}

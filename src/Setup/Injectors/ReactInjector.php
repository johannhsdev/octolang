<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

final class ReactInjector extends BaseInjector
{
    public function stack(): string
    {
        return 'react';
    }

    public function inject(string $path, string $original): void
    {
        $importSwitcher = "import LocaleSwitcher from '@/components/octolang/LocaleSwitcher';";
        $importHook     = "import { useOctoLang } from '@/hooks/useOctoLang';";

        $modified = preg_replace(
            '/(<nav\b[^>]*>)/i',
            '$1'."\n                        <LocaleSwitcher />",
            $original,
            limit: 1,
            count: $count,
        );

        if ($count === 0) {
            $modified = preg_replace(
                '/(<header\b[^>]*>)/i',
                '$1'."\n                <LocaleSwitcher />",
                $original,
                limit: 1,
                count: $count,
            );
        }

        $body = ($count > 0 && $modified !== null) ? $modified : $original;

        $body = preg_replace(
            '/(<\/header>)/i',
            '$1'."\n".$this->reactTranslationBlock(),
            $body,
            limit: 1,
        ) ?? $body;

        $body = preg_replace(
            '/(const\s*\{[^}]+\}\s*=\s*usePage[^;]*;)/i',
            '$1'."\n    const { __ } = useOctoLang();",
            $body,
            limit: 1,
            count: $hookCount,
        ) ?? $body;

        if ($hookCount === 0) {
            $body = preg_replace(
                '/(export default function \w+[^{]*\{)/i',
                '$1'."\n    const { __ } = useOctoLang();",
                $body,
                limit: 1,
            ) ?? $body;
        }

        file_put_contents($path, $this->jsNote().PHP_EOL.$importSwitcher.PHP_EOL.$importHook.PHP_EOL.$body);

        $this->recordMutation($path, 'welcome_react_import', [
            'type' => 'exact_line',
            'line' => $importSwitcher,
        ]);
    }

    private function reactTranslationBlock(): string
    {
        return <<<'JSX'
            <div className="w-full max-w-[335px] text-center mb-6 lg:max-w-4xl">
                <p className="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                    {__('messages.welcome.octolang_thanks')}
                </p>
                <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {__('messages.welcome.octolang_status')}
                </p>
                <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {__('messages.welcome.octolang_hint')}
                </p>
            </div>
        JSX;
    }

    private function jsNote(): string
    {
        return <<<'JS'
            /*
            * octolang:processed
            * ┌──────────────────────────────────────────────────────────────────────┐
            * │  OctoLang — Multi-language Package                                   │
            * │  Este archivo fue detectado. OctoLang no modificó su contenido.      │
            * │                                                                      │
            * │  Consulta la documentación para integrar traducciones en Inertia:    │
            * │  https://github.com/johannhsdev/octolang                             │
            * └──────────────────────────────────────────────────────────────────────┘
            */
        JS;
    }
}

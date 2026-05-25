<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

final class BladeInjector extends BaseInjector
{
    public function stack(): string
    {
        return 'blade';
    }

    public function inject(string $path, string $original): void
    {
        if ($original === '') {
            $this->ensureParentDirectoryExists($path);
            copy($this->stubsDir.'/welcome.blade.php', $path);
            $this->recordCreatedFile($path, 'welcome');
            return;
        }

        $withWidget = preg_replace(
            '/(<body\b[^>]*>)/i',
            '$1'."\n".$this->indentedBlock(),
            $original,
            limit: 1,
        );

        file_put_contents($path, $this->htmlNote()."\n".$withWidget);

        $this->recordMutation($path, 'welcome_html_note', [
            'type'         => 'marked_block',
            'start_marker' => self::HTML_NOTE_START,
            'end_marker'   => self::HTML_NOTE_END,
        ]);

        $this->recordMutation($path, 'welcome_blade_block', [
            'type'         => 'marked_block',
            'start_marker' => self::BLOCK_START,
            'end_marker'   => self::BLOCK_END,
        ]);
    }

    private function indentedBlock(): string
    {
        return preg_replace('/^/m', '    ', $this->bladeBlock()) ?? $this->bladeBlock();
    }

    private function bladeBlock(): string
    {
        return <<<'BLADE'
            <!-- octolang:block:start -->
            <x-octolang::locale-switcher />
            <div class="w-full max-w-4xl text-center mb-6 mt-14 lg:mt-4">
                <p class="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                    {{ __('octolang::messages.welcome.octolang_thanks') }}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('octolang::messages.welcome.octolang_status') }}
                </p>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('octolang::messages.welcome.octolang_hint') }}
                </p>
            </div>
            <!-- octolang:block:end -->
        BLADE;
    }
}

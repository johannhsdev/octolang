<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

final class LivewireInjector extends BaseInjector
{
    public function stack(): string
    {
        return 'livewire';
    }

    public function inject(string $path, string $original): void
    {
        $modified = preg_replace(
            '/<\/head>/i',
            '    @livewireStyles'."\n".'    </head>',
            $original,
            limit: 1,
        ) ?? $original;

        $modified = preg_replace(
            '/(<body\b[^>]*>)/i',
            '$1'."\n".$this->indentedLivewireBlock(),
            $modified,
            limit: 1,
        ) ?? $modified;

        $modified = preg_replace(
            '/<\/body>/i',
            '    @livewireScripts'."\n".'    </body>',
            $modified,
            limit: 1,
        ) ?? $modified;

        file_put_contents($path, $this->htmlNote()."\n".$modified);

        $this->recordMutation($path, 'welcome_html_note', [
            'type'         => 'marked_block',
            'start_marker' => self::HTML_NOTE_START,
            'end_marker'   => self::HTML_NOTE_END,
        ]);

        $this->recordMutation($path, 'welcome_livewire_block', [
            'type'         => 'marked_block',
            'start_marker' => self::BLOCK_START,
            'end_marker'   => self::BLOCK_END,
        ]);
    }

    private function indentedLivewireBlock(): string
    {
        return preg_replace('/^/m', '    ', $this->livewireBlock()) ?? $this->livewireBlock();
    }

    private function livewireBlock(): string
    {
        return <<<'BLADE'
            <!-- octolang:block:start -->
            <livewire:octolang-switch />
            <!-- octolang:block:end -->
        BLADE;
    }
}

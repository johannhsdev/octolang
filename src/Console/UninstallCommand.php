<?php

namespace Johannhsdev\OctoLang\Console;

use Illuminate\Console\Command;
use Johannhsdev\OctoLang\Setup\UninstallManager;

class UninstallCommand extends Command
{
    protected $signature = 'octolang:uninstall';

    protected $description = 'Safely remove OctoLang-managed files and edits.';

    public function handle(UninstallManager $manager): int
    {
        $summary = $manager->uninstall();

        $this->info('OctoLang uninstall summary');
        $this->newLine();

        $this->renderSection('Removed', $summary['removed']);
        $this->renderSection('Skipped', $summary['skipped']);
        $this->renderSection('Manual review', $summary['manual'], warn: true);

        return self::SUCCESS;
    }

    private function renderSection(string $title, array $items, bool $warn = false): void
    {
        $warn ? $this->warn($title.':') : $this->line($title.':');

        if ($items === []) {
            $this->line('  - none');
            $this->newLine();
            return;
        }

        foreach ($items as $item) {
            $this->line('  - '.$item);
        }

        $this->newLine();
    }
}

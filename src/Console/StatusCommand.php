<?php

namespace Johannhsdev\OctoLang\Console;

use Illuminate\Console\Command;
use Johannhsdev\OctoLang\Setup\InstallManifest;

class StatusCommand extends Command
{
    protected $signature = 'octolang:status';

    protected $description = 'Show the files and mutations tracked by OctoLang.';

    public function handle(InstallManifest $manifest): int
    {
        if (! $manifest->exists()) {
            $this->warn('OctoLang manifest not found. Run octolang:install first.');

            return self::SUCCESS;
        }

        $files     = $manifest->trackedFiles();
        $mutations = $manifest->trackedMutations();

        $this->info('Tracked files ('.count($files).')');
        $this->table(['Path', 'Category'], $files);
        $this->newLine();

        $this->info('Tracked mutations ('.count($mutations).')');
        $mutations === [] ? $this->line('  none') : $this->table(['Path', 'Mutation'], $mutations);

        return self::SUCCESS;
    }
}

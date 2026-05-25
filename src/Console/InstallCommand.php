<?php

namespace Johannhsdev\OctoLang\Console;

use Illuminate\Console\Command;
use Johannhsdev\OctoLang\Setup\OctoLangInstaller;
use Johannhsdev\OctoLang\Setup\WelcomeHandler;

class InstallCommand extends Command
{
    protected $signature = 'octolang:install';

    protected $description = 'Install OctoLang resources and welcome-page integration.';

    public function handle(OctoLangInstaller $installer, WelcomeHandler $welcomeHandler): int
    {
        $installer->synchronize();
        $welcomeHandler->handle();

        $this->info('OctoLang installed successfully.');
        $this->line('Run `php artisan octolang:status` to review tracked files.');

        return self::SUCCESS;
    }
}

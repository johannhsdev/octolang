<?php

namespace Johannhsdev\OctoLang\Setup;

use Johannhsdev\OctoLang\Setup\Contracts\InjectorInterface;
use Johannhsdev\OctoLang\Setup\Injectors\BaseInjector;

class WelcomeHandler
{
    // Backward-compatible constant aliases — external code referencing WelcomeHandler::BLOCK_START, etc. continues to work.
    public const MARKER             = BaseInjector::MARKER;
    public const BLOCK_START        = BaseInjector::BLOCK_START;
    public const BLOCK_END          = BaseInjector::BLOCK_END;
    public const HTML_NOTE_START    = BaseInjector::HTML_NOTE_START;
    public const HTML_NOTE_END      = BaseInjector::HTML_NOTE_END;
    public const SCRIPT_BLOCK_START = BaseInjector::SCRIPT_BLOCK_START;
    public const SCRIPT_BLOCK_END   = BaseInjector::SCRIPT_BLOCK_END;
    public const VUE_BLOCK_START    = BaseInjector::VUE_BLOCK_START;
    public const VUE_BLOCK_END      = BaseInjector::VUE_BLOCK_END;

    /** @param  InjectorInterface[]  $injectors  Keyed by stack name. */
    public function __construct(
        private readonly StackDetector $detector,
        private readonly array $injectors,
        private readonly ?InstallManifest $manifest = null,
    ) {}

    public function handle(): void
    {
        $stack = $this->detector->detect();

        if ($stack === 'unknown') {
            return;
        }

        $path = $this->detector->resolvePath($stack);

        if ($path === null) {
            return;
        }

        $contents = file_exists($path) ? file_get_contents($path) : '';

        if (str_contains($contents, self::MARKER)) {
            return;
        }

        $injector = $this->injectors[$stack] ?? null;
        $injector?->inject($path, $contents);
    }
}

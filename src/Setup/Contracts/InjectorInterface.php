<?php

namespace Johannhsdev\OctoLang\Setup\Contracts;

interface InjectorInterface
{
    /**
     * Inject OctoLang UI into the welcome file at the given absolute path.
     * $original contains the already-read file contents (empty string if file does not exist).
     */
    public function inject(string $path, string $original): void;

    /**
     * Returns the stack name this injector handles (matches StackDetector::detect() output).
     */
    public function stack(): string;
}

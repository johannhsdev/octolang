<?php

namespace Johannhsdev\OctoLang\Setup\Injectors;

use Johannhsdev\OctoLang\Setup\Contracts\InjectorInterface;
use Johannhsdev\OctoLang\Setup\InstallManifest;
use Johannhsdev\OctoLang\Setup\StackDetector;

abstract class BaseInjector implements InjectorInterface
{
    public const MARKER             = 'octolang:processed';
    public const BLOCK_START        = '<!-- octolang:block:start -->';
    public const BLOCK_END          = '<!-- octolang:block:end -->';
    public const HTML_NOTE_START    = '<!-- octolang:note:start -->';
    public const HTML_NOTE_END      = '<!-- octolang:note:end -->';
    public const SCRIPT_BLOCK_START = '// octolang:start';
    public const SCRIPT_BLOCK_END   = '// octolang:end';
    public const VUE_BLOCK_START    = '<!-- octolang:start -->';
    public const VUE_BLOCK_END      = '<!-- octolang:end -->';

    public function __construct(
        protected readonly string $stubsDir,
        protected readonly StackDetector $detector,
        protected readonly ?InstallManifest $manifest = null,
    ) {}

    protected function relativePath(string $path): ?string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedBase = str_replace('\\', '/', rtrim($this->detector->basePath(), '/\\'));

        if (! str_starts_with($normalizedPath, $normalizedBase.'/')) {
            return null;
        }

        return ltrim(substr($normalizedPath, strlen($normalizedBase)), '/');
    }

    protected function recordMutation(string $path, string $id, array $payload): void
    {
        $relativePath = $this->relativePath($path);

        if ($relativePath !== null) {
            $this->manifest?->recordMutation($relativePath, $id, $payload);
        }
    }

    protected function recordCreatedFile(string $path, string $category): void
    {
        $relativePath = $this->relativePath($path);

        if ($relativePath !== null) {
            $this->manifest?->recordCreatedFile($relativePath, $category);
        }
    }

    protected function ensureParentDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    protected function htmlNote(): string
    {
        return <<<'HTML'
            <!-- octolang:note:start -->
            <!--
            octolang:processed
            ┌──────────────────────────────────────────────────────────────────────┐
            │  OctoLang — Multi-language Package                                   │
            │  Este archivo fue detectado como personalizado.                      │
            │  OctoLang no modificó su contenido.                                  │
            │                                                                      │
            │  Widget de idiomas añadido automáticamente tras la etiqueta <body>.  │
            │  Para usar traducciones en este archivo:                             │
            │    __('octolang::messages.grupo.clave')                              │
            │                                                                      │
            │  Documentación: https://github.com/johannhsdev/octolang              │
            └──────────────────────────────────────────────────────────────────────┘
            -->
            <!-- octolang:note:end -->
        HTML;
    }
}

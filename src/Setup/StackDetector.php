<?php

namespace Johannhsdev\OctoLang\Setup;

class StackDetector
{
    public function __construct(private readonly string $basePath) {}

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function detect(): string
    {
        if ($this->resolveSveltePath() !== null) {
            return 'svelte';
        }

        if (file_exists($this->basePath.'/resources/js/Pages/Welcome.vue')) {
            return 'vue';
        }

        if ($this->resolveReactPath() !== null) {
            return 'react';
        }

        if ($this->isLivewireApp()) {
            return 'livewire';
        }

        if (file_exists($this->basePath.'/resources/views/welcome.blade.php')) {
            return 'blade';
        }

        if (is_dir($this->basePath.'/resources/views')) {
            return 'blade';
        }

        return 'unknown';
    }

    public function resolvePath(string $stack): ?string
    {
        return match ($stack) {
            'blade', 'livewire' => $this->basePath.'/resources/views/welcome.blade.php',
            'vue'               => $this->basePath.'/resources/js/Pages/Welcome.vue',
            'react'             => $this->resolveReactPath(),
            'svelte'            => $this->resolveSveltePath(),
            default             => null,
        };
    }

    private function isLivewireApp(): bool
    {
        return class_exists(\Livewire\Livewire::class)
            && file_exists($this->basePath.'/resources/views/welcome.blade.php');
    }

    private function resolveSveltePath(): ?string
    {
        $candidates = [
            '/resources/js/Pages/Welcome.svelte',
            '/resources/js/Pages/welcome.svelte',
        ];

        foreach ($candidates as $relative) {
            if (file_exists($this->basePath.$relative)) {
                return $this->basePath.$relative;
            }
        }

        return null;
    }

    private function resolveReactPath(): ?string
    {
        $candidates = [
            '/resources/js/Pages/Welcome.jsx',
            '/resources/js/Pages/Welcome.tsx',
            '/resources/js/pages/welcome.jsx',
            '/resources/js/pages/welcome.tsx',
        ];

        foreach ($candidates as $relative) {
            if (file_exists($this->basePath.$relative)) {
                return $this->basePath.$relative;
            }
        }

        return null;
    }
}

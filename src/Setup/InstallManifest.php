<?php

namespace Johannhsdev\OctoLang\Setup;

class InstallManifest
{
    public function __construct(
        private readonly string $basePath,
        private readonly string $relativePath = 'storage/app/octolang/install.json',
    ) {}

    public function all(): array
    {
        if (! file_exists($this->path())) {
            return [
                'files' => [],
                'mutations' => [],
            ];
        }

        $decoded = json_decode((string) file_get_contents($this->path()), true);

        if (! is_array($decoded)) {
            return [
                'files' => [],
                'mutations' => [],
            ];
        }

        return [
            'files' => is_array($decoded['files'] ?? null) ? $decoded['files'] : [],
            'mutations' => is_array($decoded['mutations'] ?? null) ? $decoded['mutations'] : [],
        ];
    }

    public function path(): string
    {
        return $this->basePath.'/'.$this->relativePath;
    }

    public function exists(): bool
    {
        return file_exists($this->path());
    }

    /** @return array<int, array{path: string, category: string}> */
    public function trackedFiles(): array
    {
        $rows = [];
        foreach ($this->all()['files'] as $path => $entry) {
            $rows[] = ['path' => $path, 'category' => $entry['category'] ?? 'unknown'];
        }

        return $rows;
    }

    /** @return array<int, array{path: string, mutation: string}> */
    public function trackedMutations(): array
    {
        $rows = [];
        foreach ($this->all()['mutations'] as $path => $mutations) {
            foreach ($mutations as $mutation) {
                $rows[] = ['path' => $path, 'mutation' => $mutation['id'] ?? 'unknown'];
            }
        }

        return $rows;
    }

    public function file(string $relativePath): ?array
    {
        $data = $this->all();

        return $data['files'][$this->normalize($relativePath)] ?? null;
    }

    public function mutationsFor(string $relativePath): array
    {
        $data = $this->all();

        return array_values($data['mutations'][$this->normalize($relativePath)] ?? []);
    }

    public function recordCreatedFile(string $relativePath, string $category): void
    {
        $normalized = $this->normalize($relativePath);
        $absolute = $this->absolutePath($normalized);
        $data = $this->all();

        $data['files'][$normalized] = [
            'category' => $category,
            'hash' => file_exists($absolute) ? md5_file($absolute) : null,
        ];

        $this->write($data);
    }

    public function recordMutation(string $relativePath, string $id, array $payload): void
    {
        $normalized = $this->normalize($relativePath);
        $data = $this->all();

        $data['mutations'][$normalized][$id] = array_merge($payload, [
            'id' => $id,
        ]);

        $this->write($data);
    }

    public function forgetFile(string $relativePath): void
    {
        $normalized = $this->normalize($relativePath);
        $data = $this->all();

        unset($data['files'][$normalized]);

        $this->write($data);
    }

    public function forgetMutation(string $relativePath, string $id): void
    {
        $normalized = $this->normalize($relativePath);
        $data = $this->all();

        unset($data['mutations'][$normalized][$id]);

        if (($data['mutations'][$normalized] ?? []) === []) {
            unset($data['mutations'][$normalized]);
        }

        $this->write($data);
    }

    public function absolutePath(string $relativePath): string
    {
        return $this->basePath.'/'.$this->normalize($relativePath);
    }

    private function write(array $data): void
    {
        $files = $data['files'] ?? [];
        $mutations = $data['mutations'] ?? [];

        ksort($files);
        ksort($mutations);

        foreach ($mutations as &$items) {
            ksort($items);
        }

        if ($files === [] && $mutations === []) {
            if (file_exists($this->path())) {
                unlink($this->path());
            }

            return;
        }

        $directory = dirname($this->path());

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $this->path(),
            json_encode([
                'files' => $files,
                'mutations' => $mutations,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function normalize(string $path): string
    {
        return str_replace('\\', '/', ltrim($path, '/\\'));
    }
}

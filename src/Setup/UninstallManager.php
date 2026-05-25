<?php

namespace Johannhsdev\OctoLang\Setup;

class UninstallManager
{
    public function __construct(
        private readonly string $basePath,
        private readonly InstallManifest $manifest,
    ) {}

    public function uninstall(): array
    {
        if (! $this->manifest->exists()) {
            return [
                'removed' => [],
                'skipped' => [],
                'manual' => $this->residualCandidates(),
            ];
        }

        $summary = [
            'removed' => [],
            'skipped' => [],
            'manual' => [],
        ];

        $data = $this->manifest->all();

        foreach ($data['mutations'] ?? [] as $relativePath => $mutations) {
            $this->revertMutations($relativePath, array_values($mutations), $summary);
        }

        foreach ($data['files'] ?? [] as $relativePath => $entry) {
            $this->removeTrackedFile($relativePath, $entry, $summary);
        }

        $this->cleanupEmptyOctoLangViewDirectories($summary);

        return $summary;
    }

    private function revertMutations(string $relativePath, array $mutations, array &$summary): void
    {
        $absolutePath = $this->manifest->absolutePath($relativePath);

        if (! file_exists($absolutePath)) {
            foreach ($mutations as $mutation) {
                $this->manifest->forgetMutation($relativePath, $mutation['id']);
                $summary['skipped'][] = $relativePath.' (missing target for mutation '.$mutation['id'].')';
            }

            return;
        }

        $contents = (string) file_get_contents($absolutePath);
        $updated = $contents;

        foreach ($mutations as $mutation) {
            $result = match ($mutation['type'] ?? null) {
                'exact_line' => $this->removeExactLine($updated, (string) ($mutation['line'] ?? '')),
                'marked_block' => $this->removeMarkedBlock(
                    $updated,
                    (string) ($mutation['start_marker'] ?? ''),
                    (string) ($mutation['end_marker'] ?? ''),
                ),
                'exact_fragment' => $this->removeExactFragment($updated, (string) ($mutation['fragment'] ?? '')),
                default => null,
            };

            if ($result === null) {
                $summary['manual'][] = $relativePath.' (mutation '.$mutation['id'].' requires manual review)';
                continue;
            }

            $updated = $result;
            $this->manifest->forgetMutation($relativePath, $mutation['id']);
            $summary['removed'][] = $relativePath.' (mutation '.$mutation['id'].')';
        }

        if ($updated !== $contents) {
            file_put_contents($absolutePath, $updated);
        }
    }

    private function removeTrackedFile(string $relativePath, array $entry, array &$summary): void
    {
        $absolutePath = $this->manifest->absolutePath($relativePath);

        if (! file_exists($absolutePath)) {
            $this->manifest->forgetFile($relativePath);
            $summary['skipped'][] = $relativePath.' (already missing)';
            return;
        }

        $currentHash = md5_file($absolutePath);
        $recordedHash = $entry['hash'] ?? null;

        if ($recordedHash !== null && $currentHash !== $recordedHash) {
            $summary['manual'][] = $relativePath.' (file changed after install)';
            return;
        }

        unlink($absolutePath);
        $this->manifest->forgetFile($relativePath);
        $summary['removed'][] = $relativePath;
    }

    private function removeExactLine(string $contents, string $line): ?string
    {
        if ($line === '' || ! str_contains($contents, $line)) {
            return null;
        }

        return preg_replace('/^\h*'.preg_quote($line, '/').'\r?\n?/m', '', $contents, 1) ?? $contents;
    }

    private function removeMarkedBlock(string $contents, string $startMarker, string $endMarker): ?string
    {
        if ($startMarker === '' || $endMarker === '') {
            return null;
        }

        $pattern = '/[ \t]*'.preg_quote($startMarker, '/').'.*?'.preg_quote($endMarker, '/').'\s*/s';

        if (! preg_match($pattern, $contents)) {
            return null;
        }

        return preg_replace($pattern, '', $contents, 1);
    }

    private function removeExactFragment(string $contents, string $fragment): ?string
    {
        if ($fragment === '' || ! str_contains($contents, $fragment)) {
            return null;
        }

        return str_replace($fragment, '', $contents);
    }

    private function residualCandidates(): array
    {
        $candidates = [];

        $welcome = $this->basePath.'/resources/views/welcome.blade.php';
        $appCss = $this->basePath.'/resources/css/app.css';
        $octoCss = $this->basePath.'/resources/css/octolang.css';
        $vendorView = $this->basePath.'/resources/views/vendor/octolang/components/locale-switcher.blade.php';

        if (file_exists($welcome) && str_contains((string) file_get_contents($welcome), 'octolang:processed')) {
            $candidates[] = 'resources/views/welcome.blade.php';
        }

        if (file_exists($appCss) && str_contains((string) file_get_contents($appCss), '@import "./octolang.css";')) {
            $candidates[] = 'resources/css/app.css';
        }

        if (file_exists($octoCss)) {
            $candidates[] = 'resources/css/octolang.css';
        }

        if (file_exists($vendorView)) {
            $candidates[] = 'resources/views/vendor/octolang/components/locale-switcher.blade.php';
        }

        return $candidates;
    }

    private function cleanupEmptyOctoLangViewDirectories(array &$summary): void
    {
        $directories = [
            'resources/views/vendor/octolang/components',
            'resources/views/vendor/octolang',
        ];

        foreach ($directories as $relativePath) {
            $absolutePath = $this->manifest->absolutePath($relativePath);

            if (! is_dir($absolutePath)) {
                continue;
            }

            $items = array_diff(scandir($absolutePath) ?: [], ['.', '..']);

            if ($items !== []) {
                continue;
            }

            rmdir($absolutePath);
            $summary['removed'][] = $relativePath.' (empty directory)';
        }
    }
}

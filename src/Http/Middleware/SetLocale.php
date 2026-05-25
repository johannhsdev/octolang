<?php

namespace Johannhsdev\OctoLang\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Johannhsdev\OctoLang\LocaleManager;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(protected LocaleManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->manager->current();

        App::setLocale($locale);

        $this->shareWithInertia($locale);

        return $next($request);
    }

    private function shareWithInertia(string $locale): void
    {
        if (! class_exists(\Inertia\Inertia::class)) {
            return;
        }

        \Inertia\Inertia::share([
            'locale'            => $locale,
            'supported_locales' => $this->manager->supported(),
            'translations'      => $this->loadTranslations($locale),
        ]);
    }

    public function getTranslations(string $locale): array
    {
        return $this->loadTranslations($locale);
    }

    private function loadTranslations(string $locale): array
    {
        // Auto-discover all files in the octolang:: namespace and in the host
        // application's lang/{locale} directory so React/Inertia can consume
        // both package and app translations through the shared props.
        $packageLangDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $locale;

        $packageDirs = array_filter([
            is_dir($packageLangDir) ? $packageLangDir : null,
            resource_path('lang/vendor/octolang/' . $locale),
        ], 'is_dir');

        $appLangDir = app()->langPath() . DIRECTORY_SEPARATOR . $locale;
        $appDirs = array_filter([
            is_dir($appLangDir) ? $appLangDir : null,
        ], 'is_dir');

        $packageFiles = $this->discoverTranslationFiles($packageDirs);
        $appFiles = $this->discoverTranslationFiles($appDirs);

        $translations = [];
        foreach (array_keys($packageFiles + $appFiles) as $name) {
            $packageTranslations = isset($packageFiles[$name])
                ? trans('octolang::' . $name, [], $locale)
                : [];
            $appTranslations = isset($appFiles[$name])
                ? trans($name, [], $locale)
                : [];

            $translations[$name] = $this->mergeTranslations($packageTranslations, $appTranslations);
        }

        return $translations;
    }

    private function discoverTranslationFiles(array $dirs): array
    {
        $files = [];
        foreach ($dirs as $dir) {
            $pattern = str_replace('\\', '/', $dir) . '/*.php';
            foreach (glob($pattern) as $path) {
                $files[basename($path, '.php')] = true;
            }
        }

        return $files;
    }

    private function mergeTranslations(mixed $packageTranslations, mixed $appTranslations): mixed
    {
        if (is_array($packageTranslations) && is_array($appTranslations)) {
            return array_replace_recursive($packageTranslations, $appTranslations);
        }

        if ($appTranslations !== []) {
            return $appTranslations;
        }

        return $packageTranslations;
    }
}

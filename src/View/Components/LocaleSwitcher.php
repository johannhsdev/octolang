<?php

namespace Johannhsdev\OctoLang\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Johannhsdev\OctoLang\LocaleManager;

class LocaleSwitcher extends Component
{
    public readonly array  $supported;
    public readonly string $current;
    public readonly string $source;
    public readonly string $routeName;
    public readonly array  $flags;

    protected array $flagMap = [
        'es' => '🇪🇸',
        'en' => '🇺🇸',
        'fr' => '🇫🇷',
        'pt' => '🇧🇷',
        'de' => '🇩🇪',
        'it' => '🇮🇹',
        'ja' => '🇯🇵',
        'zh' => '🇨🇳',
        'ar' => '🇸🇦',
        'ru' => '🇷🇺',
    ];

    public function __construct()
    {
        $manager = app(LocaleManager::class);

        $this->supported = (array) config('locale.supported', []);
        $this->current   = $manager->current();
        $this->source    = $manager->currentSource();
        $this->routeName = (string) config('locale.route_name', 'locale.store');
        $this->flags     = $this->flagMap;
    }

    public function flag(string $locale): ?string
    {
        return $this->flagMap[$locale] ?? null;
    }

    public function render(): View
    {
        return view('octolang::components.locale-switcher');
    }
}

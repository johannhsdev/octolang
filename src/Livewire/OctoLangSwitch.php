<?php

namespace Johannhsdev\OctoLang\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Johannhsdev\OctoLang\LocaleManager;
use Livewire\Component;

class OctoLangSwitch extends Component
{
    public string $locale = '';

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

    public function mount(): void
    {
        $this->locale = app(LocaleManager::class)->current();
    }

    /**
     * Switch the application locale to the given value.
     *
     * If the locale is unsupported it is silently ignored, matching the
     * behaviour of LocaleManager::set() which is a no-op for unknown locales.
     */
    public function switchLocale(string $locale): void
    {
        $manager = app(LocaleManager::class);

        if (! $manager->isSupported($locale)) {
            return;
        }

        $manager->set($locale);

        $this->locale = $manager->current();

        App::setLocale($this->locale);

        $this->dispatch('octolang:locale-changed');
    }

    /**
     * Build the full presentation data for every supported locale button.
     *
     * The view receives a ready-to-use array so it never needs to compute
     * styles, colours, or active state by itself.
     *
     * @return array<string, array{
     *     locale:         string,
     *     flag:           string|null,
     *     hasFlag:        bool,
     *     isActive:       bool,
     *     showDefaultDot: bool,
     *     buttonStyle:    string,
     *     labelColor:     string,
     * }>
     */
    public function localeButtons(): array
    {
        $manager = app(LocaleManager::class);
        $source  = $manager->currentSource();
        $buttons = [];

        foreach ($manager->supported() as $loc) {
            $isActive = $loc === $this->locale;
            $flag     = $this->flagMap[$loc] ?? null;
            $hasFlag  = $flag !== null;

            $buttons[$loc] = [
                'locale'         => $loc,
                'flag'           => $flag,
                'hasFlag'        => $hasFlag,
                'isActive'       => $isActive,
                'showDefaultDot' => $isActive && $source === 'default',
                'buttonStyle'    => $this->buildButtonStyle($isActive, $hasFlag),
                'labelColor'     => $isActive ? '#ffffff' : '#1e293b',
            ];
        }

        return $buttons;
    }

    public function render(): View
    {
        return view('octolang::livewire.octo-lang-switch', [
            'buttons' => $this->localeButtons(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the inline button style string for the given active/flag state.
     *
     * Kept private because it is a pure presentation detail with no external
     * callers; it exists only to keep localeButtons() readable.
     */
    private function buildButtonStyle(bool $isActive, bool $hasFlag): string
    {
        $background = $hasFlag || ! $isActive ? 'transparent' : '#1e293b';
        $ring       = $hasFlag && $isActive ? 'box-shadow:0 0 0 2px #1e293b;' : '';
        $opacity    = $isActive ? '1' : '0.4';
        $scale      = $isActive ? ($hasFlag ? 'scale(1.15)' : 'scale(1.05)') : 'scale(1)';

        return 'all:unset;'
            . 'box-sizing:border-box;'
            . 'position:relative;'
            . 'cursor:pointer;'
            . 'font-size:1.35rem;'
            . 'line-height:1;'
            . 'display:inline-flex;'
            . 'align-items:center;'
            . 'justify-content:center;'
            . 'width:2rem;'
            . 'height:2rem;'
            . 'border-radius:9999px;'
            . 'transition:opacity .15s ease,transform .15s ease,box-shadow .15s ease;'
            . "background:{$background};"
            . "opacity:{$opacity};"
            . "transform:{$scale};"
            . $ring;
    }
}

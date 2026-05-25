<?php

namespace Johannhsdev\OctoLang;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Session\SessionManager;

class LocaleManager
{
    public function __construct(
        protected Config         $config,
        protected SessionManager $session,
    ) {}

    public function set(string $locale): void
    {
        if ($this->isSupported($locale)) {
            if ($locale === $this->default()) {
                $this->clear();

                return;
            }

            $this->session->put($this->sessionKey(), $locale);
        }
    }

    public function current(): string
    {
        $locale = $this->session->get($this->sessionKey(), $this->default());

        return is_string($locale) && $locale !== '' && $this->isSupported($locale)
            ? $locale
            : $this->default();
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supported(), strict: true);
    }

    public function supported(): array
    {
        return (array) $this->config->get('locale.supported', []);
    }

    public function default(): string
    {
        $appLocale = $this->config->get('app.locale');
        if (is_string($appLocale) && $appLocale !== '') {
            return $appLocale;
        }

        return 'en';
    }

    private function sessionKey(): string
    {
        return (string) $this->config->get('locale.session_key', 'locale');
    }

    public function clear(): void
    {
        $this->session->forget($this->sessionKey());
    }

    public function isUsingSessionOverride(): bool
    {
        $locale = $this->session->get($this->sessionKey());

        return is_string($locale) && $locale !== '' && $this->isSupported($locale);
    }

    public function currentSource(): string
    {
        return $this->isUsingSessionOverride() ? 'session' : 'default';
    }
}

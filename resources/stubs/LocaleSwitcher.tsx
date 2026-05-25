// octolang:processed
// Componente generado por OctoLang. Personaliza libremente.
// Sin props — los datos vienen automáticamente del middleware OctoLang vía Inertia.
//
// Equivalencias con Blade:
//   useOctoLang().__('messages.switcher.label')  →  __('octolang::messages.switcher.label')
//   switchLocale('en')                           →  POST /locale

import { useOctoLang } from '@/hooks/useOctoLang'

export default function LocaleSwitcher() {
    const { locale, locale_source, supported_locales, switchLocale, __ } = useOctoLang()

    return (
        <nav
            id="octolang-switcher"
            aria-label={__('messages.switcher.label')}
            className="octolang-switcher"
        >
            {supported_locales?.map((loc) => (
                <button
                    key={loc}
                    onClick={() => switchLocale(loc)}
                    data-active={loc === locale ? 'true' : 'false'}
                    data-source={loc === locale ? locale_source : 'available'}
                    title={`${__('messages.switcher.tooltip')} (${loc.toUpperCase()})`}
                    aria-label={`${__('messages.switcher.tooltip')}: ${loc.toUpperCase()}`}
                    aria-current={loc === locale ? 'true' : 'false'}
                    className={`octolang-btn ${loc === locale ? 'octolang-btn--active' : 'octolang-btn--inactive'} ${loc === locale && locale_source === 'default' ? 'octolang-btn--following-default' : ''}`}
                >
                    <span className="octolang-label">{loc.toUpperCase()}</span>
                    {loc === locale && locale_source === 'default' && (
                        <span className="octolang-status-dot" aria-hidden="true" />
                    )}
                </button>
            ))}
        </nav>
    )
}

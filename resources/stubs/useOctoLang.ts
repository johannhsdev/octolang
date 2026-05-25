// octolang:processed
// Hook generado por OctoLang. Úsalo en cualquier componente React.
// Equivalente al helper __('octolang::messages.grupo.clave') de Blade.
//
// Uso:
//   const { __, locale, switchLocale } = useOctoLang()
//   __('messages.welcome.octolang_thanks')  → octolang::messages (siempre disponible)
//   __('home.hero.title')                   → home.php (si existe en el namespace octolang)

import { router, usePage } from '@inertiajs/react'

interface TranslationMap {
    [key: string]: string | TranslationMap | undefined
}

interface OctoLangProps {
    locale: string
    default_locale: string
    locale_source: 'default' | 'session'
    supported_locales: string[]
    translations: {
        messages: TranslationMap
        [namespace: string]: TranslationMap
    }
    [key: string]: unknown
}

export function useOctoLang() {
    const { locale, default_locale, locale_source, supported_locales, translations } = usePage<OctoLangProps>().props

    const switchLocale = (loc: string) => {
        router.post('/locale', { locale: loc })
    }

    /**
     * Obtiene una traducción del namespace OctoLang.
     * Equivalente a __('octolang::file.group.key') en Blade.
     *
     * @param key  Clave con formato "archivo.grupo.clave" o "grupo.clave" (usa 'messages' por defecto)
     *
     * @example
     *   __('messages.welcome.octolang_thanks')
     *   __('welcome.octolang_thanks')   // shorthand — busca en 'messages'
     *   __('home.hero.title')           // archivo home.php
     */
    const __ = (key: string): string => {
        const parts = key.split('.')

        // Determinar si el primer segmento es un namespace conocido
        const knownNamespace = translations && parts[0] in translations
        const segments = knownNamespace ? parts : ['messages', ...parts]

        const [namespace, ...rest] = segments

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        let value: any = (translations as any)?.[namespace]
        for (const segment of rest) {
            if (value == null || typeof value !== 'object') return key
            value = value[segment]
        }

        return typeof value === 'string' ? value : key
    }

    return { locale, default_locale, locale_source, supported_locales, translations, switchLocale, __ }
}

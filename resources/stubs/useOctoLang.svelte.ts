// octolang:processed
// Store generado por OctoLang. Personaliza libremente.
// Los datos vienen automáticamente del middleware OctoLang vía Inertia shared props.
//
// Uso en componentes .svelte:
//   import { getLocale, getTranslations, switchLocale, __ } from '@/stores/useOctoLang'
//   getLocale()                                → locale activo (reactivo vía $derived)
//   __('messages.welcome.octolang_thanks')     → traducción actual

import { page, router } from '@inertiajs/svelte'

type TranslationMap = {
    [key: string]: string | TranslationMap | undefined
}

interface OctoLangProps {
    locale: string
    default_locale: string
    locale_source: 'session' | 'default'
    supported_locales: string[]
    translations: {
        messages: TranslationMap
        [file: string]: TranslationMap
    }
    [key: string]: unknown
}

function props(): OctoLangProps {
    return page.props as OctoLangProps
}

// Getters reactivos — úsalos dentro de $derived() en componentes Svelte 5
export function getLocale(): string {
    return props().locale
}

export function getDefaultLocale(): string {
    return props().default_locale
}

export function getLocaleSource(): 'session' | 'default' {
    return props().locale_source
}

export function getSupportedLocales(): string[] {
    return props().supported_locales ?? []
}

export function getTranslations(): OctoLangProps['translations'] {
    return props().translations
}

export function switchLocale(loc: string): void {
    router.post('/locale', { locale: loc })
}

export function __(key: string): string {
    const t = props().translations
    if (!t) return key

    const parts = key.split('.')
    const knownFile = parts[0] in t
    const [file, ...rest] = knownFile ? parts : ['messages', ...parts]

    let value: unknown = t[file]

    for (const seg of rest) {
        if (value == null || typeof value !== 'object') return key
        value = (value as Record<string, unknown>)[seg]
    }

    return typeof value === 'string' ? value : key
}

// octolang:processed
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

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

export function useOctoLang() {
    const page = usePage<OctoLangProps>()

    const switchLocale = (locale: string): void => {
        router.post('/locale', { locale })
    }

    const __ = (key: string): string => {
        const translations = page.props.translations
        const parts = key.split('.')

        const knownFile = translations && parts[0] in translations
        const segments = knownFile ? parts : ['messages', ...parts]

        const [file, ...rest] = segments
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        let value: any = (translations as any)?.[file]

        for (const segment of rest) {
            if (value == null || typeof value !== 'object') return key
            value = value[segment]
        }

        return typeof value === 'string' ? value : key
    }

    return {
        locale: computed(() => page.props.locale),
        default_locale: computed(() => page.props.default_locale),
        locale_source: computed(() => page.props.locale_source),
        supported_locales: computed(() => page.props.supported_locales),
        translations: computed(() => page.props.translations),
        switchLocale,
        __,
    }
}

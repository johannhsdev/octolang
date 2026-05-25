<!-- octolang:processed -->
<script lang="ts">
    import { getLocale, getLocaleSource, getSupportedLocales, switchLocale, __ } from '@/stores/useOctoLang'

    const locale           = $derived(getLocale())
    const locale_source    = $derived(getLocaleSource())
    const supported_locales = $derived(getSupportedLocales())
</script>

<nav
    id="octolang-switcher"
    aria-label={__('messages.switcher.label')}
    class="octolang-switcher"
>
    {#each supported_locales as loc (loc)}
        <button
            data-active={loc === locale ? 'true' : 'false'}
            data-source={loc === locale ? locale_source : 'available'}
            title="{__('messages.switcher.tooltip')} ({loc.toUpperCase()})"
            aria-label="{__('messages.switcher.tooltip')}: {loc.toUpperCase()}"
            aria-current={loc === locale ? 'true' : 'false'}
            class="octolang-btn {loc === locale ? 'octolang-btn--active' : 'octolang-btn--inactive'}"
            onclick={() => switchLocale(loc)}
        >
            <span class="octolang-label">{loc.toUpperCase()}</span>
            {#if loc === locale && locale_source === 'default'}
                <span class="octolang-status-dot" aria-hidden="true"></span>
            {/if}
        </button>
    {/each}
</nav>

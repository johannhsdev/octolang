<!-- octolang:processed -->
<script setup lang="ts">
import { useOctoLang } from '@/composables/useOctoLang'

const { locale, locale_source, supported_locales, switchLocale, __ } = useOctoLang()
</script>

<template>
    <nav
        id="octolang-switcher"
        :aria-label="__('messages.switcher.label')"
        class="octolang-switcher"
    >
        <button
            v-for="loc in supported_locales"
            :key="loc"
            :data-active="loc === locale ? 'true' : 'false'"
            :data-source="loc === locale ? locale_source : 'available'"
            :title="`${__('messages.switcher.tooltip')} (${loc.toUpperCase()})`"
            :aria-label="`${__('messages.switcher.tooltip')}: ${loc.toUpperCase()}`"
            :aria-current="loc === locale ? 'true' : 'false'"
            :class="[
                'octolang-btn',
                loc === locale ? 'octolang-btn--active' : 'octolang-btn--inactive',
                loc === locale && locale_source === 'default' ? 'octolang-btn--following-default' : '',
            ]"
            @click="switchLocale(loc)"
        >
            <span class="octolang-label">{{ loc.toUpperCase() }}</span>
            <span
                v-if="loc === locale && locale_source === 'default'"
                class="octolang-status-dot"
                aria-hidden="true"
            />
        </button>
    </nav>
</template>

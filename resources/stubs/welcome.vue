<!-- octolang:processed -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import OctoLangSwitch from '@/components/octolang/OctoLangSwitch.vue'
import { useOctoLang } from '@/composables/useOctoLang'

withDefaults(defineProps<{ canRegister?: boolean }>(), { canRegister: true })

const { __ } = useOctoLang()
</script>

<template>
    <Head :title="__('messages.welcome.title')" />

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">

        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <!-- octolang:start -->
                <OctoLangSwitch />
                <!-- octolang:end -->
            </nav>
        </header>

        <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
            <div class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC]">
                <h1 class="mb-1 font-medium">
                    {{ __('messages.welcome.octolang_thanks') }}
                </h1>
                <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('messages.welcome.octolang_status') }}
                </p>
                <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                    {{ __('messages.welcome.octolang_hint') }}
                </p>
            </div>
        </main>

    </div>
</template>

{{--
  Este stub es solo una referencia del bloque que OctoLang inyecta
  en el welcome.blade.php de una app Blade.
  No se usa para sobreescribir — la inyección es siempre quirúrgica.
--}}

<!-- octolang:block:start -->
<x-octolang::locale-switcher />
<div class="w-full max-w-4xl text-center mb-6 mt-14 lg:mt-4">
    <p class="text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
        {{ __('octolang::messages.welcome.octolang_thanks') }}
    </p>
    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
        {{ __('octolang::messages.welcome.octolang_status') }}
    </p>
    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
        {{ __('octolang::messages.welcome.octolang_hint') }}
    </p>
</div>
<!-- octolang:block:end -->

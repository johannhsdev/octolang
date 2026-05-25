<nav
    id="octolang-switcher"
    aria-label="{{ __('octolang::messages.switcher.label') }}"
    class="octolang-switcher"
>
    @foreach ($supported as $locale)
        @php $flag = $flags[$locale] ?? null; @endphp

        <form method="POST" action="{{ route($routeName) }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">
            <button
                type="submit"
                data-active="{{ $locale === $current ? 'true' : 'false' }}"
                data-source="{{ $locale === $current ? $source : 'available' }}"
                title="{{ __('octolang::messages.switcher.tooltip') }} ({{ strtoupper($locale) }})"
                aria-label="{{ __('octolang::messages.switcher.tooltip') }}: {{ strtoupper($locale) }}"
                aria-current="{{ $locale === $current ? 'true' : 'false' }}"
                class="octolang-btn {{ $locale === $current ? 'octolang-btn--active' : 'octolang-btn--inactive' }} {{ $locale === $current && $source === 'default' ? 'octolang-btn--following-default' : '' }}"
            >
                @if ($flag)
                    <span aria-hidden="true">{{ $flag }}</span>
                @else
                    <span class="octolang-label">{{ strtoupper($locale) }}</span>
                @endif

                @if ($locale === $current && $source === 'default')
                    <span class="octolang-status-dot" aria-hidden="true"></span>
                @endif
            </button>
        </form>
    @endforeach
</nav>

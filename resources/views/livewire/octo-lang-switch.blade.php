<div>
    <nav
        id="octolang-switcher"
        aria-label="{{ __('octolang::messages.switcher.label') }}"
        style="position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:row;gap:0.375rem;align-items:center;background:rgba(255,255,255,0.85);backdrop-filter:blur(4px);border:1px solid rgba(0,0,0,0.08);border-radius:9999px;padding:0.375rem 0.75rem;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1);"
    >
        @foreach ($buttons as $btn)
            <button
                type="button"
                wire:click="switchLocale('{{ $btn['locale'] }}')"
                data-active="{{ $btn['isActive'] ? 'true' : 'false' }}"
                data-inactive="{{ $btn['isActive'] ? 'false' : 'true' }}"
                data-hasflag="{{ $btn['hasFlag'] ? 'true' : 'false' }}"
                title="{{ __('octolang::messages.switcher.tooltip') }} ({{ strtoupper($btn['locale']) }})"
                aria-label="{{ __('octolang::messages.switcher.tooltip') }}: {{ strtoupper($btn['locale']) }}"
                aria-current="{{ $btn['isActive'] ? 'true' : 'false' }}"
                style="{{ $btn['buttonStyle'] }}"
                onmouseover="if(this.dataset.inactive==='true'){this.style.opacity='0.75';this.style.transform='scale(1.1)';}"
                onmouseout="if(this.dataset.inactive==='true'){this.style.opacity='0.4';this.style.transform='scale(1)';}"
            >
                @if ($btn['hasFlag'])
                    <span aria-hidden="true" style="font-size:1.35rem;line-height:1;display:block;">{{ $btn['flag'] }}</span>
                @else
                    <span style="font-size:0.6rem;font-family:monospace;font-weight:700;display:flex;align-items:center;justify-content:center;width:100%;height:100%;border-radius:9999px;color:{{ $btn['labelColor'] }};letter-spacing:-0.025em;">
                        {{ strtoupper($btn['locale']) }}
                    </span>
                @endif

                @if ($btn['showDefaultDot'])
                    <span aria-hidden="true" style="position:absolute;right:0.125rem;bottom:0.125rem;width:0.5rem;height:0.5rem;border-radius:9999px;background:#34d399;box-shadow:0 0 0 1px #fff;"></span>
                @endif
            </button>
        @endforeach
    </nav>

    <div style="width:100%;max-width:56rem;text-align:center;margin-bottom:1.5rem;margin-top:3.5rem;">
        <p style="font-size:1rem;font-weight:500;color:#1b1b18;">
            {{ __('octolang::messages.welcome.octolang_thanks') }}
        </p>
        <p style="font-size:0.875rem;color:#706f6c;margin-top:0.25rem;">
            {{ __('octolang::messages.welcome.octolang_status') }}
        </p>
        <p style="font-size:0.875rem;color:#706f6c;">
            {{ __('octolang::messages.welcome.octolang_hint') }}
        </p>
    </div>
</div>

@script
<script>
    $wire.on('octolang:locale-changed', () => {
        window.location.reload();
    });
</script>
@endscript

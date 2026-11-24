@props(['name'])
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('save')
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2Z"/><path d="M7 21v-8h10v8M8 3v6h8V3"/>
            @break
        @case('details')
            <path d="M5 12h14m-6-6 6 6-6 6"/>
            @break
        @case('key')
            <circle cx="8" cy="9" r="5"/><path d="m11.5 12.5 7 7m-2.5-2.5 3-3m-1 5 3-3"/>
            @break
        @case('lock')
        @case('unlock')
            <rect x="5" y="11" width="14" height="10" rx="2"/>
            @if ($name === 'lock')
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
            @else
                <path d="M8 11V7a4 4 0 0 1 7.5-2"/>
            @endif
            @break
    @endswitch
</svg>

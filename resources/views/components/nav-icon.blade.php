@props(['name'])

<svg {{ $attributes->class('nav-icon') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" />
            @break
        @case('score')
            <path d="M4 5h6v14H4zM14 5h6v14h-6zM7 9v6M17 9v6M5 12h4M15 12h4" />
            @break
        @case('wallet')
            <path d="M4 6h14a2 2 0 0 1 2 2v11H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12" /><path d="M15 11h6v4h-6a2 2 0 0 1 0-4Z" />
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
            @break
        @case('session')
            <rect x="3" y="4" width="18" height="16" rx="2" /><path d="M7 8h10M7 12h6M7 16h4" />
            @break
        @case('shuttlecock')
            <path d="m8 3 8 8M5 6l8 8M11 2l6 6-3 6-6 3-6-6Z" /><path d="m14 14 5 5M16 12l6 6-4 4-6-6" />
            @break
        @case('income')
            <path d="M5 17 19 3M10 3h9v9M4 21h16" />
            @break
        @case('expense')
            <path d="m5 7 14 14M10 21h9v-9M4 3h16" />
            @break
        @case('tag')
            <path d="M20.6 13.6 11 23l-9-9V4h10Z" /><circle cx="7" cy="9" r="1" />
            @break
        @case('report')
            <path d="M4 3h16v18H4zM8 17v-4M12 17V8M16 17v-7" />
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
            @break
        @case('install')
            <path d="M12 3v12M7 10l5 5 5-5M5 21h14" />
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5M15 12H3M15 3h5a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-5" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18" />
            @break
        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>

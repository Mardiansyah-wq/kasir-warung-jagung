@php
    $paths = [
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'doc' => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h4"/><path d="M10 12h6M10 16h6"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'box' => '<path d="M12 3 3 7.5v9L12 21l9-4.5v-9z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 5.5a3 3 0 0 1 0 5.8M18 14.5c2 .8 3 2.7 3 5.5"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'store' => '<path d="M4 9h16v11H4z"/><path d="M3 9l2-5h14l2 5"/><path d="M9 20v-5h6v5"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9 7 7M17 17l2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c0-3.6 3.4-6.5 7.5-6.5s7.5 2.9 7.5 6.5"/>',
        'cart' => '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M3 4h2.5l2.2 10.5h10.8L21 8H6"/>',
        'wallet' => '<path d="M3 8a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 9.5h18"/><path d="M16.5 14.5h.01"/>',
        'coffee' => '<path d="M4 8h12v5.5a5.5 5.5 0 0 1-5.5 5.5h-1A5.5 5.5 0 0 1 4 13.5z"/><path d="M16 9.5h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M4 21h13"/>',
        'trend' => '<path d="M3 17l6-6 4 4 7-7"/><path d="M15 8h5v5"/>',
        'download' => '<path d="M12 3.5v11"/><path d="M7.5 10.5 12 15l4.5-4.5"/><path d="M4 20h16"/>',
        'calendar' => '<path d="M4.5 5.5h15v15h-15z"/><path d="M8.5 2.5v5M15.5 2.5v5M4.5 10.5h15"/>',
        'chevron-down' => '<path d="M6 9.5 12 15.5l6-6"/>',
        'help' => '<circle cx="12" cy="12" r="8.5"/><path d="M9.6 9.4a2.6 2.6 0 0 1 5 .9c0 1.7-2.3 2-2.3 3.7"/><path d="M12 17.2v.1"/>',
        'logout' => '<path d="M15 4h4v16h-4"/><path d="M11 8l4 4-4 4"/><path d="M15 12H3"/>',
    ];
@endphp

<svg
    class="nav-icon"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>{!! $paths[$name] ?? $paths['menu'] !!}</svg>

<aside x-cloak x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-[#003e43] text-slate-100 flex flex-col shadow-2xl transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0">
    <!-- Logo -->
    <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#00828c] shadow-lg shadow-[#00828c]/40">
            <svg class="h-5 w-5 text-white" viewBox="0 0 512 512" fill="currentColor">
                <g transform="translate(49.08, 130.28) scale(0.56)">
                    <path
                        d="M 417.00,45.00 L 250.00,45.00 L 44.00,404.00 L 167.00,404.00 L 333.00,116.00 L 405.00,238.00 L 466.00,132.00 Z" />
                    <path
                        d="M 695.00,45.00 L 571.00,45.00 L 404.00,333.00 L 332.00,211.00 L 271.00,317.00 L 320.00,404.00 L 488.00,404.00 Z" />
                </g>
            </svg>
        </span>
        <div>
            <p class="text-sm font-bold tracking-wide leading-none text-white">NodeHub</p>
            <p class="text-[9px] uppercase tracking-wider text-teal-200/70 mt-0.5">Centralized Infra Control</p>
        </div>

        <!-- Close button (mobile) -->
        <button type="button" class="ml-auto lg:hidden p-1 rounded-md hover:bg-white/10"
            x-on:click="sidebarOpen = false">
            <svg class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
        <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </x-slot>
            {{ __('Dashboard') }}
        </x-sidebar-link>

        <x-sidebar-link href="{{ route('computers.index') }}" :active="request()->routeIs('computers.*')">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
            </x-slot>
            {{ __('Devices') }}
        </x-sidebar-link>

        <x-sidebar-link href="{{ route('actions.index') }}" :active="request()->routeIs('actions.*')">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </x-slot>
            {{ __('Remote Actions') }}
        </x-sidebar-link>

        <x-sidebar-link href="{{ route('tags.index') }}" :active="request()->routeIs('tags.*')">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.568 3.15l8.98 8.98a2.25 2.25 0 0 1 0 3.182l-5.172 5.172a2.25 2.25 0 0 1-3.182 0l-8.98-8.98A2.25 2.25 0 0 1 0.5 9.818V4.5A2.25 2.25 0 0 1 2.75 2.25h5.318c.597 0 1.17.237 1.591.659z" />
                    <circle cx="5" cy="5" r="1" fill="currentColor" />
                </svg>
            </x-slot>
            {{ __('Tags') }}
        </x-sidebar-link>



        <x-sidebar-link href="{{ route('about') }}" :active="request()->routeIs('about')">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </x-slot>
            {{ __('About') }}
        </x-sidebar-link>
    </nav>

    <!-- Footer Credit -->
    <div class="px-4 py-3 border-t border-white/10 text-center">
        <p class="text-[11px] text-teal-200/70 leading-snug">
            Made with <span class="text-rose-400">&hearts;</span> by
            <a href="https://seno21.github.io/" target="_blank" rel="noopener noreferrer"
                class="font-semibold text-white hover:text-teal-200 transition underline-offset-2">
                nzucode
            </a>
        </p>
    </div>
</aside>

<!-- Backdrop (mobile) -->
<div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
    x-on:click="sidebarOpen = false"></div>

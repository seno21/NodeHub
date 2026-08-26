<!-- Mobile Bottom Navigation Bar -->
<div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200/80 lg:hidden shadow-lg pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-5 h-16 max-w-md mx-auto px-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center gap-1 transition-colors relative {{ request()->routeIs('dashboard') ? 'text-[#00828c] font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            @if(request()->routeIs('dashboard'))
                <span class="absolute top-0 w-8 h-1 bg-[#00828c] rounded-b-full"></span>
            @endif
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ request()->routeIs('dashboard') ? '2.2' : '1.8' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
            <span class="text-[10px] tracking-tight truncate">{{ __('Home') }}</span>
        </a>

        <!-- Devices -->
        <a href="{{ route('computers.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-colors relative {{ request()->routeIs('computers.*') ? 'text-[#00828c] font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            @if(request()->routeIs('computers.*'))
                <span class="absolute top-0 w-8 h-1 bg-[#00828c] rounded-b-full"></span>
            @endif
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ request()->routeIs('computers.*') ? '2.2' : '1.8' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
            </svg>
            <span class="text-[10px] tracking-tight truncate">{{ __('Devices') }}</span>
        </a>

        <!-- Actions -->
        <a href="{{ route('actions.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-colors relative {{ request()->routeIs('actions.*') ? 'text-[#00828c] font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            @if(request()->routeIs('actions.*'))
                <span class="absolute top-0 w-8 h-1 bg-[#00828c] rounded-b-full"></span>
            @endif
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ request()->routeIs('actions.*') ? '2.2' : '1.8' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <span class="text-[10px] tracking-tight truncate">{{ __('Actions') }}</span>
        </a>

        <!-- Tags -->
        <a href="{{ route('tags.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-colors relative {{ request()->routeIs('tags.*') ? 'text-[#00828c] font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            @if(request()->routeIs('tags.*'))
                <span class="absolute top-0 w-8 h-1 bg-[#00828c] rounded-b-full"></span>
            @endif
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ request()->routeIs('tags.*') ? '2.2' : '1.8' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.15c.677-.07 1.354-.07 2.032 0A12.753 12.753 0 0 1 20.85 12.33a12.753 12.753 0 0 1-9.25 9.25c-.678.07-1.355.07-2.033 0a12.753 12.753 0 0 1-9.25-9.25c-.07-.678-.07-1.355 0-2.033a12.753 12.753 0 0 1 9.25-9.25Z" />
            </svg>
            <span class="text-[10px] tracking-tight truncate">{{ __('Tags') }}</span>
        </a>

        <!-- About -->
        <a href="{{ route('about') }}"
           class="flex flex-col items-center justify-center gap-1 transition-colors relative {{ request()->routeIs('about') ? 'text-[#00828c] font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            @if(request()->routeIs('about'))
                <span class="absolute top-0 w-8 h-1 bg-[#00828c] rounded-b-full"></span>
            @endif
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ request()->routeIs('about') ? '2.2' : '1.8' }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <span class="text-[10px] tracking-tight truncate">{{ __('About') }}</span>
        </a>
    </div>
</div>

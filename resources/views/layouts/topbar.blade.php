<!-- Top bar -->
<div class="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-md border-b border-gray-200/80 flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 shadow-2xs">
    <!-- Hamburger & Mobile Branding (mobile) -->
    <div class="flex items-center gap-2.5 lg:hidden">
        <button type="button"
                class="rounded-xl p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition active:scale-95"
                x-on:click="sidebarOpen = true">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>

        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#00828c] text-white shadow-xs">
                <svg class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor">
                    <g transform="translate(49.08, 130.28) scale(0.56)">
                        <path d="M 417.00,45.00 L 250.00,45.00 L 44.00,404.00 L 167.00,404.00 L 333.00,116.00 L 405.00,238.00 L 466.00,132.00 Z" />
                        <path d="M 695.00,45.00 L 571.00,45.00 L 404.00,333.00 L 332.00,211.00 L 271.00,317.00 L 320.00,404.00 L 488.00,404.00 Z" />
                    </g>
                </svg>
            </span>
            <span class="text-sm font-bold text-gray-900 tracking-tight">NodeHub</span>
        </a>
    </div>

    <div class="flex-1"></div>

    <!-- User Profile Dropdown & Logout -->
    <div class="relative flex items-center gap-3" x-data="{ userMenuOpen: false }">
        <button type="button"
                x-on:click="userMenuOpen = !userMenuOpen"
                x-on:click.outside="userMenuOpen = false"
                class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-gray-100/80 transition focus:outline-none">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#00828c] to-[#00585f] text-xs font-bold text-white uppercase shadow-sm">
                {{ Auth::user()->initials() }}
            </span>
            <div class="text-left hidden sm:block">
                <p class="text-xs font-bold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-gray-500 leading-tight">{{ Auth::user()->email }}</p>
            </div>
            <svg class="h-4 w-4 text-gray-400 transition transform" x-bind:class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="userMenuOpen"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 top-12 z-50 w-56 rounded-2xl bg-white p-2 border border-gray-100 shadow-xl"
             style="display: none;">

            <div class="px-3 py-2 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>

            <div class="py-1">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-700 hover:bg-[#00828c]/10 hover:text-[#00828c] transition">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                    {{ __('Profile') }}
                </a>
            </div>

            <div class="border-t border-gray-100 pt-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 transition"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                        </svg>
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

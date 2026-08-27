@if ($computers->isEmpty())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
        <div class="p-14 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/>
                </svg>
            </span>
            @if (request('search') || request('tag') || request('os'))
                <h3 class="mt-4 text-base font-semibold text-gray-900">
                    {{ __('Tidak ada perangkat yang ditemukan') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Coba gunakan kata kunci lain atau hapus filter pencarian.') }}
                </p>
            @else
                <h3 class="mt-4 text-base font-semibold text-gray-900">
                    {{ __('No devices registered yet') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Add your first computer to start remote access.') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('computers.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-[#00828c] rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-[#006e76] transition">
                        {{ __('Add Device') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Mobile card list (visible on screens < md) --}}
    <div class="space-y-3.5 md:hidden">
        @foreach ($computers as $computer)
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 space-y-3 transition hover:shadow-md hover:border-[#00828c]/40">
                <!-- Header: Status & OS -->
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-2 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200/60">
                        <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                              x-bind:class="statusClass({{ $computer->id }})"></span>
                        <span class="text-xs font-semibold text-gray-700"
                              x-text="statusLabel({{ $computer->id }})"></span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $computer->os_type === 'windows' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-orange-50 text-orange-700 border border-orange-100' }}">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            @if ($computer->os_type === 'windows')
                                <path fill="#0078D4" d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z"/>
                            @else
                                <circle cx="12" cy="12" r="8.5" fill="none" stroke="#E95420" stroke-width="2.2"/>
                                <circle cx="12" cy="5.5" r="2" fill="#E95420"/>
                                <circle cx="6.4" cy="15.25" r="2" fill="#E95420"/>
                                <circle cx="17.6" cy="15.25" r="2" fill="#E95420"/>
                            @endif
                        </svg>
                        {{ ucfirst($computer->os_type) }}
                    </span>
                </div>

                <!-- Body: Device Name & Details -->
                <div>
                    <h3 class="font-bold text-base text-gray-900 leading-snug">{{ $computer->name }}</h3>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700">{{ $computer->ip_address }}:{{ $computer->vnc_port }}</span>
                        @if ($computer->location)
                            <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-slate-600">
                                <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                {{ $computer->location }}
                            </span>
                        @endif
                    </div>
                    @if ($computer->tagsRelation && $computer->tagsRelation->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($computer->tagsRelation as $tagItem)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium"
                                      style="background-color: {{ $tagItem->color ? $tagItem->color . '20' : '#e2e8f0' }}; color: {{ $tagItem->color ?? '#475569' }}; border: 1px solid {{ $tagItem->color ? $tagItem->color . '40' : '#cbd5e1' }}">
                                    {{ $tagItem->name }}
                                </span>
                            @endforeach
                        </div>
                    @elseif ($computer->tags)
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach (array_map('trim', explode(',', $computer->tags)) as $tagName)
                                @if ($tagName !== '')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $tagName }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if ($computer->description)
                        <p class="mt-2 text-xs text-gray-600 line-clamp-2">{{ $computer->description }}</p>
                    @endif
                </div>

                <!-- Actions Footer -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                    <form method="POST" action="{{ route('computers.connect', $computer) }}"
                          data-name="{{ $computer->name }}"
                          x-on:submit.prevent="connect($event, {{ $computer->id }})"
                          class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#00828c] border border-transparent rounded-xl text-xs font-bold uppercase tracking-wider text-white hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none transition shadow-xs disabled:opacity-60 disabled:cursor-not-allowed"
                                x-bind:disabled="pendingId !== null || connecting">
                            <svg class="h-3.5 w-3.5 animate-spin" x-show="isButtonLoading({{ $computer->id }})" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="!isButtonLoading({{ $computer->id }})">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            {{ __('Connect') }}
                        </button>
                    </form>

                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button"
                                title="{{ __('Cek Diagnosa Ping & Port') }}"
                                class="rounded-xl p-2.5 text-gray-500 hover:text-[#00828c] hover:bg-[#00828c]/10 bg-slate-100 transition"
                                x-on:click.prevent="ping({{ $computer->id }}, '{{ $computer->ip_address }}', {{ $computer->vnc_port }}, '{{ route('computers.ping', $computer) }}')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 7.5 .415-.207a.75.75 0 0 1 1.085.67V10.5m6-3-.415-.207a.75.75 0 0 0-1.085.67V10.5M6.75 16.5h.008v.008h-.008v-.008Zm2.25 0h.008v.008H9v-.008Zm2.25 0h.008v.008H12v-.008Zm2.25 0h.008v.008h-.008v-.008ZM4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75Z"/>
                            </svg>
                        </button>

                        <a href="{{ route('computers.edit', $computer) }}"
                           class="rounded-xl p-2.5 text-gray-500 hover:text-[#00828c] hover:bg-[#00828c]/10 bg-slate-100 transition"
                           title="{{ __('Edit') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                        </a>

                        <button type="button"
                                class="rounded-xl p-2.5 text-gray-500 hover:text-red-600 hover:bg-red-50 bg-slate-100 transition"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-computer-deletion-{{ $computer->id }}')"
                                title="{{ __('Delete') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Desktop table view (visible on screens >= md) --}}
    <div class="hidden md:block bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-widest text-gray-500">
                    <th scope="col" class="px-5 py-3">{{ __('Status') }}</th>
                    <th scope="col" class="px-5 py-3">{{ __('Device') }}</th>
                    <th scope="col" class="px-5 py-3 hidden md:table-cell">{{ __('Location') }}</th>
                    <th scope="col" class="px-5 py-3 hidden md:table-cell">{{ __('Address') }}</th>
                    <th scope="col" class="px-5 py-3 hidden lg:table-cell">{{ __('OS') }}</th>
                    <th scope="col" class="px-5 py-3 hidden lg:table-cell">{{ __('Description') }}</th>
                    <th scope="col" class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($computers as $computer)
                    <tr class="group transition hover:bg-blue-50/40">
                        {{-- Status --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                                      x-bind:class="statusClass({{ $computer->id }})"></span>
                                <span class="text-xs font-medium text-gray-500 min-w-[52px]"
                                      x-text="statusLabel({{ $computer->id }})"></span>
                            </span>
                        </td>

                        {{-- Name --}}
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-gray-900 leading-snug">{{ $computer->name }}</p>
                            <p class="text-xs text-gray-500 md:hidden mt-0.5">{{ $computer->ip_address }}:{{ $computer->vnc_port }}</p>
                            @if ($computer->location)
                                <span class="inline-flex items-center gap-1 text-[11px] text-gray-600 md:hidden mt-1 bg-slate-100 px-2 py-0.5 rounded-md">
                                    <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    {{ $computer->location }}
                                </span>
                            @endif
                            @if ($computer->tagsRelation && $computer->tagsRelation->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($computer->tagsRelation as $tagItem)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium"
                                              style="background-color: {{ $tagItem->color ? $tagItem->color . '20' : '#e2e8f0' }}; color: {{ $tagItem->color ?? '#475569' }}; border: 1px solid {{ $tagItem->color ? $tagItem->color . '40' : '#cbd5e1' }}">
                                            {{ $tagItem->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @elseif ($computer->tags)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (array_map('trim', explode(',', $computer->tags)) as $tagName)
                                        @if ($tagName !== '')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $tagName }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        {{-- Location --}}
                        <td class="px-5 py-3.5 whitespace-nowrap hidden md:table-cell">
                            @if ($computer->location)
                                <span class="inline-flex items-center gap-1.5 text-xs text-slate-700 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/60">
                                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span class="truncate max-w-[140px]">{{ $computer->location }}</span>
                                </span>
                            @else
                                <span class="text-xs text-gray-400 font-normal">-</span>
                            @endif
                        </td>

                        {{-- Address --}}
                        <td class="px-5 py-3.5 whitespace-nowrap hidden md:table-cell">
                            <span class="font-mono text-xs text-gray-600">{{ $computer->ip_address }}:{{ $computer->vnc_port }}</span>
                        </td>

                        {{-- OS --}}
                        <td class="px-5 py-3.5 whitespace-nowrap hidden lg:table-cell">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $computer->os_type === 'windows' ? 'bg-blue-50 text-blue-700' : 'bg-orange-50 text-orange-700' }}">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    @if ($computer->os_type === 'windows')
                                        <path fill="#0078D4" d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z"/>
                                    @else
                                        <circle cx="12" cy="12" r="8.5" fill="none" stroke="#E95420" stroke-width="2.2"/>
                                        <circle cx="12" cy="5.5" r="2" fill="#E95420"/>
                                        <circle cx="6.4" cy="15.25" r="2" fill="#E95420"/>
                                        <circle cx="17.6" cy="15.25" r="2" fill="#E95420"/>
                                    @endif
                                </svg>
                                {{ ucfirst($computer->os_type) }}
                            </span>
                        </td>

                        {{-- Description --}}
                        <td class="px-5 py-3.5 hidden lg:table-cell">
                            @if ($computer->description)
                                <span class="text-xs text-gray-600 line-clamp-2 max-w-xs" title="{{ $computer->description }}">{{ $computer->description }}</span>
                            @else
                                <span class="text-xs text-gray-400 font-normal">-</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1">
                                <form method="POST" action="{{ route('computers.connect', $computer) }}"
                                      data-name="{{ $computer->name }}"
                                      x-on:submit.prevent="connect($event, {{ $computer->id }})">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#00828c] border border-transparent rounded-md text-[11px] font-semibold uppercase tracking-widest text-white hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                                            x-bind:disabled="pendingId !== null || connecting">
                                        <svg class="h-3 w-3 animate-spin" x-show="isButtonLoading({{ $computer->id }})" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="!isButtonLoading({{ $computer->id }})">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                        </svg>
                                        {{ __('Connect') }}
                                    </button>
                                </form>

                                <button type="button"
                                        title="{{ __('Cek Diagnosa Ping & Port') }}"
                                        class="rounded-md p-1.5 text-gray-400 hover:text-[#00828c] hover:bg-[#00828c]/10 transition"
                                        x-on:click.prevent="ping({{ $computer->id }}, '{{ $computer->ip_address }}', {{ $computer->vnc_port }}, '{{ route('computers.ping', $computer) }}')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m8.25 7.5 .415-.207a.75.75 0 0 1 1.085.67V10.5m6-3-.415-.207a.75.75 0 0 0-1.085.67V10.5M6.75 16.5h.008v.008h-.008v-.008Zm2.25 0h.008v.008H9v-.008Zm2.25 0h.008v.008H12v-.008Zm2.25 0h.008v.008h-.008v-.008ZM4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75Z"/>
                                    </svg>
                                </button>

                                <a href="{{ route('computers.edit', $computer) }}"
                                   class="rounded-md p-1.5 text-gray-400 hover:text-[#00828c] hover:bg-[#00828c]/10 transition"
                                   title="{{ __('Edit') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                    </svg>
                                </a>

                                <button type="button"
                                        class="rounded-md p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                        x-on:click.prevent="$dispatch('open-modal', 'confirm-computer-deletion-{{ $computer->id }}')"
                                        title="{{ __('Delete') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($computers->hasPages())
        <div class="mt-6">
            {{ $computers->links() }}
        </div>
    @endif
@endif

@foreach ($computers as $computer)
    <x-modal name="confirm-computer-deletion-{{ $computer->id }}" maxWidth="md" :show="false" focusable>
        <form method="post" action="{{ route('computers.destroy', $computer) }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 shadow-xs">
                    <svg class="h-5.5 w-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="text-base font-bold text-gray-900 leading-snug">
                        {{ __('Konfirmasi Hapus Perangkat') }}
                    </h2>
                    <p class="mt-0.5 text-xs text-rose-600 font-medium">
                        {{ __('Tindakan ini permanen dan tidak dapat dibatalkan.') }}
                    </p>
                </div>
            </div>

            {{-- Device Card Preview --}}
            <div class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/>
                            </svg>
                        </span>
                        <span class="font-semibold text-sm text-slate-900 truncate">{{ $computer->name }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shrink-0 {{ $computer->os_type === 'windows' ? 'bg-blue-100/80 text-blue-700' : 'bg-orange-100/80 text-orange-700' }}">
                        {{ ucfirst($computer->os_type) }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-200/60">
                    <span class="font-mono text-slate-600">{{ $computer->ip_address }}:{{ $computer->vnc_port }}</span>
                    @if ($computer->location)
                        <span class="truncate max-w-[150px] text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px]">
                            {{ $computer->location }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5">
                <button type="button"
                        x-on:click="$dispatch('close')"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    {{ __('Batal') }}
                </button>

                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 font-semibold text-xs shadow-xs shadow-rose-200 transition duration-150 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                    {{ __('Ya, Hapus Device') }}
                </button>
            </div>
        </form>
    </x-modal>
@endforeach

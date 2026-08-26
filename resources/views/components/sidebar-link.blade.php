@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all '.
        ($active
            ? 'bg-[#00828c] text-white shadow-md shadow-[#00828c]/30 font-semibold'
            : 'text-slate-200 hover:bg-white/10 hover:text-white'),
]) }}>
    <span {{ $active ? 'class="text-white"' : 'class="text-slate-300"' }}>{{ $icon }}</span>
    {{ $slot }}
</a>

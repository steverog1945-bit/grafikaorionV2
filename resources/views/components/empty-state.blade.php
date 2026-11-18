@props([
    'title'       => 'Belum ada data',
    'description' => null,
])

<div class="empty-state">
    @isset($icon)
        <div class="w-16 h-16 rounded-2xl bg-[var(--surface-secondary,var(--color-slate-100))] text-[var(--text-secondary,var(--color-slate-400))] flex items-center justify-center mx-auto mb-4">{{ $icon }}</div>
    @endisset

    <p class="font-medium text-slate-500">{{ $title }}</p>

    @if($description)
        <p class="text-xs text-slate-400 mt-1">{{ $description }}</p>
    @endif
</div>

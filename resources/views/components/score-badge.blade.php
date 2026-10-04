@props([
    'score' => null,
    'count' => 0,
    'label' => null,
    'size' => 'md',
    'showCount' => true,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'score-sm',
        'lg' => 'score-lg',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    @if ($score)
        <span class="score {{ $sizeClass }}">{{ number_format($score, 1) }}</span>
        <div class="leading-tight">
            @if ($label)
                <div class="font-semibold" style="font-size:14px; color:var(--ink-900);">{{ $label }}</div>
            @endif
            @if ($showCount)
                <div class="muted" style="font-size:12.5px;">
                    {{ $count }} {{ Str::plural('review', $count) }}
                </div>
            @endif
        </div>
    @else
        <span class="score score-none {{ $sizeClass }}">—</span>
        <span class="muted" style="font-size:12.5px;">No reviews yet</span>
    @endif
</div>

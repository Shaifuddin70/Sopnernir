@php
    $paidPct = $summaryTotal > 0
        ? number_format(($summaryPaid / $summaryTotal) * 100, 0, '.', '')
        : '0';
    $pillClass = 'inline-flex h-11 items-center gap-1.5 rounded-full px-3.5 text-sm font-semibold';
@endphp

<div class="flex flex-wrap items-center gap-2">
    <span class="{{ $pillClass }} border border-line bg-surface-card text-foreground-muted">
        {{ __('Total') }}
        <span class="tabular-nums text-foreground">{{ $summaryTotal }}</span>
    </span>
    <span class="{{ $pillClass }} border border-success/25 bg-success-muted text-success">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
        {{ __('Paid') }}
        <span class="tabular-nums">{{ $summaryPaid }}</span>
    </span>
    <span class="{{ $pillClass }} border border-warning/25 bg-warning-muted text-warning">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>
        {{ __('Not paid') }}
        <span class="tabular-nums">{{ $summaryUnpaid }}</span>
    </span>
    @if ($summaryTotal > 0)
        <span class="{{ $pillClass }} border border-primary/20 bg-primary-muted tabular-nums text-primary">
            {{ __(':pct% paid', ['pct' => $paidPct]) }}
        </span>
    @endif
</div>

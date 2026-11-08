@php
    $paidPct = $summaryTotal > 0
        ? number_format(($summaryPaid / $summaryTotal) * 100, 0, '.', '')
        : '0';
    $pillClass = 'monthly-payments-summary__pill';
@endphp

<div class="monthly-payments-summary">
    <span class="{{ $pillClass }} monthly-payments-summary__pill--muted">
        {{ __('Total') }}
        <span class="tabular-nums text-foreground">{{ $summaryTotal }}</span>
    </span>
    <span class="{{ $pillClass }} monthly-payments-summary__pill--paid">
        <span class="monthly-payments-summary__dot monthly-payments-summary__dot--paid" aria-hidden="true"></span>
        {{ __('Paid') }}
        <span class="tabular-nums">{{ $summaryPaid }}</span>
    </span>
    <span class="{{ $pillClass }} monthly-payments-summary__pill--unpaid">
        <span class="monthly-payments-summary__dot monthly-payments-summary__dot--unpaid" aria-hidden="true"></span>
        {{ __('Not paid') }}
        <span class="tabular-nums">{{ $summaryUnpaid }}</span>
    </span>
    @if ($summaryTotal > 0)
        <span class="{{ $pillClass }} monthly-payments-summary__pill--pct tabular-nums">
            {{ __(':pct% paid', ['pct' => $paidPct]) }}
        </span>
    @endif
</div>

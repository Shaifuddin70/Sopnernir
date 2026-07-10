@if ($rows->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant px-4 py-10 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No rows match your search.') }}
            @else
                {{ __('No active users found.') }}
            @endif
        </p>
    </div>
@else
    @php
        $allPaid = $rows->every(fn (array $row) => $row['is_paid']);
        $somePaid = $rows->contains(fn (array $row) => $row['is_paid']);
    @endphp

    <form
        id="monthly-payments-bulk-form"
        method="post"
        action="{{ route('admin.monthly-payments.bulk-update') }}"
        class="hidden"
    >
        @csrf
        @method('patch')
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <input type="hidden" name="paid" value="0" data-bulk-paid-input>
        @foreach ($rows as $row)
            <input type="hidden" name="user_ids[]" value="{{ $row['user_id'] }}">
        @endforeach
    </form>

    <div class="ui-glass-table-wrap overflow-x-auto">
        <table class="ui-table min-w-full text-xs sm:text-sm">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Investor') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Phone') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="hidden md:table-cell">{{ __('Paid at') }}</th>
                    <th class="text-center">
                        <div class="inline-flex items-center justify-center gap-2">
                            <span>{{ __('Paid') }}</span>
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                data-check-all-paid
                                @checked($allPaid)
                                aria-label="{{ __('Check all') }}"
                                title="{{ __('Check all') }}"
                            >
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr @class(['bg-success-muted/30' => $row['is_paid']])>
                        <x-table-serial-cell :index="$loop->index" />
                        <td>
                            <div class="font-medium text-foreground">{{ $row['name'] }}</div>
                            <div class="text-[11px] text-foreground-muted sm:hidden">{{ $row['email'] }}</div>
                        </td>
                        <td class="hidden tabular-nums text-foreground-muted sm:table-cell">{{ $row['phone'] ?? '—' }}</td>
                        <td>
                            @if ($row['is_paid'])
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-success/25 bg-success-muted px-3 py-1 text-xs font-semibold text-success">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                                    {{ __('Paid') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-warning/25 bg-warning-muted px-3 py-1 text-xs font-semibold text-warning">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>
                                    {{ __('Unpaid') }}
                                </span>
                            @endif
                        </td>
                        <td class="hidden tabular-nums text-foreground-muted md:table-cell">
                            {{ $row['paid_at'] ?? '—' }}
                        </td>
                        <td class="text-center">
                            <form
                                method="post"
                                action="{{ route('admin.monthly-payments.update') }}"
                                class="inline-flex justify-center"
                            >
                                @csrf
                                @method('patch')
                                <input type="hidden" name="user_id" value="{{ $row['user_id'] }}">
                                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                                <input type="hidden" name="paid" value="{{ $row['is_paid'] ? '0' : '1' }}">
                                <label class="inline-flex cursor-pointer items-center justify-center">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                        @checked($row['is_paid'])
                                        onchange="this.form.submit()"
                                    >
                                    <span class="sr-only">
                                        {{ $row['is_paid'] ? __('Mark unpaid') : __('Mark paid') }}
                                    </span>
                                </label>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            const master = document.querySelector('[data-check-all-paid]');
            const form = document.getElementById('monthly-payments-bulk-form');
            const paidInput = form?.querySelector('[data-bulk-paid-input]');

            if (! master || ! form || ! paidInput) {
                return;
            }

            if (@json($somePaid) && ! @json($allPaid)) {
                master.indeterminate = true;
            }

            master.addEventListener('change', function () {
                paidInput.value = master.checked ? '1' : '0';
                form.submit();
            });
        })();
    </script>
@endif

@if (request()->filled('search') && ($summaryTotal ?? 0) > 0)
    <p class="mt-4 text-sm text-foreground-muted">
        {{ __('Showing :count of :total investors', ['count' => $filteredCount ?? $rows->count(), 'total' => $summaryTotal]) }}
    </p>
@endif

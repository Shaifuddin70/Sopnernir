<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between gap-2 items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">{{ $investment->title }}</h2>
            <div class="flex flex-wrap gap-2">
                <x-action-button :href="route('admin.investments.edit', [$investment, 'return' => 'show'])" variant="secondary"
                    class="px-4 py-2 font-semibold uppercase tracking-widest">{{ __('Edit') }}</x-action-button>
                <x-action-button :href="route('admin.investments.index')" variant="secondary"
                    class="px-4 py-2 font-semibold uppercase tracking-widest">{{ __('List') }}</x-action-button>
            </div>
        </div>
    </x-slot>


    <div class="space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
            <div class="border-b border-line bg-surface-secondary/90 px-6 py-4">
                <h3 class="text-base font-semibold text-foreground">{{ __('Pool overview') }}</h3>
                <p class="mt-0.5 text-xs text-foreground-muted">
                    {{ __('Lifecycle, rates, and whether investors see this pool in their portfolio.') }}</p>
            </div>
            <div class="p-6 space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $poolStatus = $investment->status;
                            $statusBadge = match ($poolStatus) {
                                \App\Models\Investment::STATUS_ACTIVE
                                    => 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-600/20',
                                \App\Models\Investment::STATUS_CLOSED
                                    => 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-600/20',
                                default => 'bg-gray-100 text-foreground ring-1 ring-inset ring-gray-500/10',
                            };
                            $statusLabel = match ($poolStatus) {
                                \App\Models\Investment::STATUS_ACTIVE => __('Active pool'),
                                \App\Models\Investment::STATUS_CLOSED => __('Closed pool'),
                                default => __('Draft'),
                            };
                            $listedBadge = $investment->is_active
                                ? 'bg-primary-muted text-primary ring-1 ring-inset ring-primary/30'
                                : 'bg-gray-100 text-foreground-muted ring-1 ring-inset ring-gray-500/10';
                        @endphp
                        <span
                            class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                            {{ $statusLabel }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $listedBadge }}">
                            {{ $investment->is_active ? __('Listed for investors') : __('Hidden from investors') }}
                        </span>
                        @if ($investment->deed_completion_deadline)
                            @php
                                $planCompleted = $investment->hasPlanCompleted();
                                $daysLeft = $investment->planCompletionDaysRemaining();
                                $planBadge = $planCompleted
                                    ? 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20'
                                    : ($daysLeft !== null && $daysLeft <= 30
                                        ? 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-600/20'
                                        : 'bg-sky-50 text-sky-900 ring-1 ring-inset ring-sky-600/15');
                            @endphp
                            <span
                                class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $planBadge }}">
                                @if ($planCompleted)
                                    {{ __('Plan completed (:date)', ['date' => $investment->deed_completion_deadline->translatedFormat('j M Y')]) }}
                                @elseif ($daysLeft === 0)
                                    {{ __('Plan ends today') }}
                                @else
                                    {{ __('Plan ends :date (:count days)', [
                                        'date' => $investment->deed_completion_deadline->translatedFormat('j M Y'),
                                        'count' => $daysLeft,
                                    ]) }}
                                @endif
                            </span>
                        @endif
                    </div>
                    <div class="shrink-0">
                        <p class="mb-2 text-xs font-medium text-foreground-muted sm:text-right">{{ __('Investor visibility') }}
                        </p>
                        <form method="post" action="{{ route('admin.investments.active', $investment) }}"
                            class="flex flex-wrap items-center gap-2 sm:justify-end">
                            @csrf
                            @method('patch')
                            @if ($investment->is_active)
                                <x-secondary-button type="submit">{{ __('Set inactive') }}</x-secondary-button>
                            @else
                                <x-primary-button type="submit">{{ __('Set active') }}</x-primary-button>
                            @endif
                        </form>
                        <p class="mt-2 max-w-md text-xs text-foreground-muted sm:text-right sm:ml-auto">
                            {{ __('Inactive pools stay in this admin list but disappear from investor portfolios and do not receive accruals.') }}
                        </p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        class="rounded-lg border border-emerald-100 bg-emerald-50/30 px-4 py-3 ring-1 ring-inset ring-emerald-600/10">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Total investment') }}</dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums text-foreground">
                            {{ number_format((float) ($investment->participants_sum_contribution_amount ?? 0), 2, '.', '') }}
                        </dd>
                        <dd class="mt-1 text-xs text-foreground-muted">{{ __('Sum of tagged contribution amounts.') }}</dd>
                    </div>
                    <div
                        class="rounded-lg border border-emerald-100 bg-emerald-50/30 px-4 py-3 ring-1 ring-inset ring-emerald-600/10">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">{{ __('Total profit') }}
                        </dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums text-foreground">
                            {{ number_format((float) ($investment->periods_sum_profit_amount ?? 0), 2, '.', '') }}</dd>
                        <dd class="mt-1 text-xs text-foreground-muted">
                            {{ __('Sum of posted monthly pool profit (all accrual months).') }}</dd>
                    </div>
                    <div class="rounded-lg border border-line bg-surface-secondary/40 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Default monthly rate') }}</dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums text-foreground">
                            {{ $investment->default_monthly_rate_pct }}<span
                                class="text-base font-normal text-foreground-muted">%</span></dd>
                    </div>
                    <div class="rounded-lg border border-line bg-surface-secondary/40 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Plan completion date') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-foreground">
                            @if ($investment->deed_completion_deadline)
                                {{ $investment->deed_completion_deadline->translatedFormat('j F Y') }}
                                <span class="mt-1 block text-xs font-normal text-foreground-muted">
                                    {{ __('Last accrual month: :month', ['month' => $investment->planCompletionMonthStart()?->translatedFormat('F Y')]) }}
                                </span>
                            @else
                                <span class="text-sm font-normal text-foreground-muted">{{ __('Not set') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-line bg-surface-secondary/40 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('First accrual month') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-foreground">
                            {{ $investment->firstAccrualMonthStart()->translatedFormat('F Y') }}
                            @if (!$investment->period_start)
                                <span
                                    class="mt-1 block text-xs font-normal text-foreground-muted">{{ __('Derived from record creation — set an explicit month on Edit if needed.') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-line bg-surface-secondary/40 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Record created') }}</dt>
                        <dd class="mt-1 font-mono text-sm font-medium text-foreground">
                            {{ $investment->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>

                <div class="rounded-lg border border-primary/30 bg-primary-muted px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ __('How accruals run') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-foreground">
                        {{ __('Each month, every tagged investor gets a profit share (pool rate × principal, split by contribution). Months from the first accrual month through the current month are filled when you save participants or when the scheduled job runs.') }}
                    </p>
                </div>

                @if ($investment->notes)
                    <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Notes') }}
                        </h4>
                        <div class="mt-2 text-sm leading-relaxed text-foreground whitespace-pre-wrap">
                            {{ $investment->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-2">{{ __('Posted monthly accruals') }}</h3>
            <p class="text-sm text-foreground-muted mb-4">
                {{ __('Each row is one calendar month of pool profit split among tagged investors.') }}</p>
            @if ($errors->has('accrual'))
                <p class="text-sm text-red-600 mb-4">{{ $errors->first('accrual') }}</p>
            @endif
            @can('accrue', $investment)
                @if ($investment->status === 'active' && $investment->is_active)
                    <div class="mb-4 rounded-md border border-line bg-surface-secondary p-4 text-sm text-foreground">
                        <p class="mb-2">
                            {{ __('Automatic fills run through :month for this pool (or until plan completion, whichever is earlier).', ['month' => $investment->lastAccrualMonthInclusive()->translatedFormat('F Y')]) }}
                        </p>
                        <form method="post" action="{{ route('admin.investments.accruals.fill-missing', $investment) }}"
                            class="inline">
                            @csrf
                            <x-secondary-button type="submit">{{ __('Fill missing accrual months') }}</x-secondary-button>
                        </form>
                        <p class="mt-2 text-xs text-foreground-muted">
                            {{ __('Creates every missing month from the pool’s first accrual month through the month above (same as the scheduled job for this pool).') }}
                        </p>
                    </div>
                @endif
            @endcan
            <x-ajax-table-region :fetch-url="route('admin.investments.show', $investment)" target-id="periods-table-fragment" ajax-fragment="periods">
                <x-table-search :fetch-url="route('admin.investments.show', $investment)" target-id="periods-table-fragment"
                    param="periods_search" ajax-fragment="periods" :placeholder="__('Search by month, profit, principal, or rate…')" />
                <div id="periods-table-fragment">
                    @include('admin.investments.partials.periods-fragment', compact('investment', 'periods'))
                </div>
            </x-ajax-table-region>

            @can('accrue', $investment)
                @if ($investment->is_active)
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-medium text-foreground mb-2">{{ __('Record one month manually') }}</h4>
                        <p class="text-xs text-foreground-muted mb-3">
                            {{ __('Use only if automatic accruals missed a month. Leave rate empty to use the pool default.') }}
                        </p>
                        <form method="post" action="{{ route('admin.investments.accruals.store', $investment) }}"
                            class="flex flex-wrap items-end gap-4">
                            @csrf
                            <div>
                                <x-input-label for="accrual_month" :value="__('Accrual month')" />
                                <input id="accrual_month" name="month" type="month"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                            </div>
                            <div>
                                <x-input-label for="applied_rate_pct" :value="__('Applied rate for this month (optional)')" />
                                <x-text-input id="applied_rate_pct" name="applied_rate_pct" type="text"
                                    class="mt-1 block w-full sm:w-32" placeholder="1.5" />
                            </div>
                            <x-primary-button type="submit">{{ __('Record accrual') }}</x-primary-button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-4">{{ __('Deed documents') }}</h3>
            <form method="post" action="{{ route('admin.investments.documents.store', $investment) }}"
                enctype="multipart/form-data" class="flex flex-wrap items-end gap-3 mb-6">
                @csrf
                <div>
                    <x-input-label for="file" :value="__('PDF or image')" />
                    <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp"
                        class="mt-1 block w-full text-sm" required />
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>
                <x-primary-button type="submit">{{ __('Upload') }}</x-primary-button>
            </form>
            <ul class="divide-y border rounded-md">
                @forelse ($investment->documents as $doc)
                    <li class="flex justify-between items-center px-4 py-2 text-sm">
                        <span>{{ $doc->original_name }}</span>
                        <div class="flex gap-3">
                            <x-action-button :href="route('admin.investments.documents.download', [$investment, $doc])">{{ __('Download') }}</x-action-button>
                            <form method="post"
                                action="{{ route('admin.investments.documents.destroy', [$investment, $doc]) }}"
                                onsubmit="return confirm('{{ __('Remove this file?') }}');">
                                @csrf
                                @method('delete')
                                <x-action-button variant="danger">{{ __('Remove') }}</x-action-button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-3 text-foreground-muted">{{ __('No documents yet.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-2">{{ __('Tagged investors') }}</h3>
            <p class="text-sm text-foreground-muted mb-4">
                {{ __('Each month, pool profit is split among everyone tagged here, in proportion to their contribution amounts.') }}
            </p>
            <form method="post" action="{{ route('admin.investments.participants.store', $investment) }}"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-8 border-b pb-6">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="user_id" :value="__('Investor')" />
                    <select id="user_id" name="user_id" data-searchable="true"
                        class="ui-select mt-1 block w-full text-sm" required>
                        <option value="">{{ __('Select user') }}</option>
                        @foreach ($investorUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})
                                @if ($u->phone)
                                    — {{ $u->phone }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="contribution_amount" :value="__('Contribution')" />
                    <x-text-input id="contribution_amount" name="contribution_amount" type="text"
                        class="mt-1 block w-full" placeholder="1000.00" value="{{ old('contribution_amount') }}" required />
                    <x-input-error :messages="$errors->get('contribution_amount')" class="mt-2" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <input type="hidden" name="include_previous_profit" value="0" />
                    <label class="inline-flex items-start gap-2 cursor-pointer">
                        <input
                            id="include_previous_profit"
                            type="checkbox"
                            name="include_previous_profit"
                            value="1"
                            class="mt-1 rounded border-line bg-surface-secondary text-primary shadow-sm focus:ring-primary"
                            @checked(old('include_previous_profit'))
                        />
                        <span class="text-sm text-foreground-muted">
                            {{ __('Add this user’s cumulative posted profit from other pools (since the month below, or app default in .env) to the contribution.') }}
                        </span>
                    </label>
                    <x-input-error :messages="$errors->get('include_previous_profit')" class="mt-2" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-input-label for="profit_carry_since_month_single" :value="__('Count profit from month (optional)')" />
                    <input
                        id="profit_carry_since_month_single"
                        name="profit_carry_since_month"
                        type="month"
                        class="mt-1 block w-full max-w-xs ui-input rounded-lg"
                        value="{{ old('profit_carry_since_month') }}"
                    />
                    <p class="mt-1 text-xs text-foreground-muted">
                        {{ __('Inclusive—sums every posted profit share from this month onward on other pools. Leave blank to use INVESTMENT_PROFIT_CARRY_SINCE_MONTH from .env, or all posted months if that is unset.') }}
                    </p>
                    <x-input-error :messages="$errors->get('profit_carry_since_month')" class="mt-2" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <x-primary-button type="submit">{{ __('Add or update participant') }}</x-primary-button>
                </div>
            </form>

            <div class="mb-8 rounded-md border border-line bg-surface-secondary p-4">
                <h4 class="text-sm font-medium text-foreground mb-2">{{ __('Tag all investors at once') }}</h4>
                <p class="text-xs text-foreground-muted mb-4">
                    {{ __('Adds every investor who is not already tagged. Each contribution is the entered amount plus that user’s cumulative posted profit from other pools from the cutoff month onward (same rules as single tag).') }}
                </p>
                <form method="post" action="{{ route('admin.investments.participants.tag-all', $investment) }}"
                    class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <x-input-label for="bulk_contribution_amount" :value="__('Contribution per investor')" />
                        <x-text-input id="bulk_contribution_amount" name="bulk_contribution_amount" type="text"
                            class="mt-1 block w-full sm:w-40" placeholder="1000.00"
                            value="{{ old('bulk_contribution_amount') }}" required />
                        <x-input-error :messages="$errors->get('bulk_contribution_amount')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="profit_carry_since_month_bulk" :value="__('Count profit from month (optional)')" />
                        <input
                            id="profit_carry_since_month_bulk"
                            name="profit_carry_since_month"
                            type="month"
                            class="mt-1 block w-full sm:w-40 ui-input rounded-lg"
                            value="{{ old('profit_carry_since_month') }}"
                        />
                        <x-input-error :messages="$errors->get('profit_carry_since_month')" class="mt-2" />
                    </div>
                    <x-primary-button type="submit">{{ __('Tag all investors') }}</x-primary-button>
                </form>
            </div>

            <x-ajax-table-region :fetch-url="route('admin.investments.show', $investment)" target-id="participants-table-fragment"
                ajax-fragment="participants">
                <x-table-search :fetch-url="route('admin.investments.show', $investment)" target-id="participants-table-fragment"
                    param="participants_search" ajax-fragment="participants" :placeholder="__('Search by investor name, email, phone, or contribution…')" />
                <div id="participants-table-fragment">
                    @include(
                        'admin.investments.partials.participants-fragment',
                        compact('investment', 'participants'))
                </div>
            </x-ajax-table-region>
        </div>

    </div>

    @if ($editingInvestment)
        <x-modal name="edit-investment" :show="true" focusable maxWidth="2xl">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h3 class="text-lg font-semibold text-foreground">{{ __('Edit investment') }}</h3>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-foreground-muted transition hover:bg-surface-secondary hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                    @click="window.location.href = '{{ route('admin.investments.show', $investment) }}'"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[calc(100vh-5rem)] overflow-y-auto px-6 py-4">
                @include('admin.investments.partials.edit-form', ['investment' => $editingInvestment, 'return' => 'show'])
            </div>
        </x-modal>
    @endif

</x-app-layout>

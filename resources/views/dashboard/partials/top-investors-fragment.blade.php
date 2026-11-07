@if ($topInvestors->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant/60 px-4 py-10 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No investors match your search.') }}
            @else
                {{ __('No investors with active investment plans yet.') }}
            @endif
        </p>
    </div>
@else
    <div class="dashboard-investor-cards space-y-2 sm:hidden">
        @foreach ($topInvestors as $row)
            <article @class([
                'dashboard-investor-card',
                'dashboard-investor-card--you' => $row->user_id === auth()->id(),
            ])>
                <div class="flex min-w-0 items-center gap-3">
                    <span class="dashboard-investor-card__rank tabular-nums">
                        {{ $topInvestors->firstItem() + $loop->index }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <x-user-identity
                            :name="$row->name"
                            :image-url="$row->profile_image_url"
                            :subtitle="$row->email"
                        />
                    </div>
                </div>
                <dl class="dashboard-investor-card__metrics">
                    <div>
                        <dt>{{ __('Tagged capital') }}</dt>
                        <dd class="tabular-nums">{{ $row->total_contribution }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Profit til today') }}</dt>
                        <dd class="tabular-nums text-success">{{ $row->profit_til_today ?? $row->total_profit }}</dd>
                    </div>
                </dl>
            </article>
        @endforeach
    </div>

    <div class="ui-glass-table-wrap hidden overflow-x-auto sm:block">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Investor') }}</th>
                    <th class="hidden sm:table-cell text-right">{{ __('Pools') }}</th>
                    <th class="text-right">{{ __('Tagged capital') }}</th>
                    <th class="hidden md:table-cell text-right">{{ __('Total profit') }}</th>
                    <th class="text-right">{{ __('Profit til today') }}</th>
                    <th class="hidden lg:table-cell text-right">{{ __('Withdrawn') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topInvestors as $row)
                    <tr @class(['bg-primary-muted/40' => $row->user_id === auth()->id()])>
                        <x-table-serial-cell :paginator="$topInvestors" :index="$loop->index" />
                        <td>
                            <x-user-identity
                                :name="$row->name"
                                :image-url="$row->profile_image_url"
                                :subtitle="$row->email"
                            />
                        </td>
                        <td class="hidden whitespace-nowrap text-right tabular-nums text-foreground-muted sm:table-cell">
                            {{ $row->pools_count ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap text-right tabular-nums">{{ $row->total_contribution }}</td>
                        <td class="hidden whitespace-nowrap text-right tabular-nums text-success md:table-cell">
                            {{ $row->total_projected_profit ?? $row->total_profit }}
                        </td>
                        <td class="whitespace-nowrap text-right tabular-nums font-semibold text-success">
                            {{ $row->profit_til_today ?? $row->total_profit }}
                        </td>
                        <td class="hidden whitespace-nowrap text-right tabular-nums text-foreground-muted lg:table-cell">
                            {{ $row->total_withdrawn ?? '0.00' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$topInvestors"
            param="top_investors_per_page"
            reset-page-key="top_investors_page"
            :fetch-url="route('dashboard')"
            target-id="dashboard-top-investors-fragment"
            ajax-fragment="top_investors"
        />
        <div class="ui-table-pagination">{{ $topInvestors->links() }}</div>
    </div>
@endif

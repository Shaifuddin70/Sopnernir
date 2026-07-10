@if ($investment->contribution_per_investor)
    <p class="mb-3 text-sm text-foreground-muted">
        {{ __('Contribution per investor from plan:') }}
        <span class="font-semibold tabular-nums text-foreground">{{ number_format((float) $investment->contribution_per_investor, 2, '.', '') }}</span>
    </p>
@else
    <p class="mb-3 text-sm text-error">
        {{ __('Set contribution per investor in Edit investment before tagging.') }}
    </p>
@endif

@if ($errors->has('contribution'))
    <p class="mb-3 text-sm text-error">{{ $errors->first('contribution') }}</p>
@endif

<div class="flex flex-col gap-3 border-b border-line pb-4 lg:flex-row lg:items-end lg:justify-between">
    @if ($investorUsers->isNotEmpty() && $investment->contribution_per_investor)
        <form method="post" action="{{ route('admin.investments.participants.store', $investment) }}" class="min-w-0 flex-1">
            @csrf
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <x-input-label for="tag_user_ids" :value="__('Select investors')" />
                    <select
                        id="tag_user_ids"
                        name="user_ids[]"
                        multiple
                        data-searchable="true"
                        placeholder="{{ __('Search and select investors…') }}"
                        class="ui-select mt-1 block w-full text-sm"
                        required
                    >
                        @foreach ($investorUsers as $u)
                            <option value="{{ $u->id }}" @selected(collect(old('user_ids', []))->contains($u->id))>
                                {{ $u->name }}@if ($u->phone) — {{ $u->phone }}@endif
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_ids')" class="mt-2" />
                    <x-input-error :messages="$errors->get('user_ids.*')" class="mt-2" />
                </div>
                <x-primary-button type="submit" class="sm:mb-0.5">{{ __('Tag selected') }}</x-primary-button>
            </div>
        </form>

        <form method="post" action="{{ route('admin.investments.participants.tag-all', $investment) }}" class="shrink-0">
            @csrf
            <x-action-button type="submit" variant="secondary">{{ __('Tag all') }}</x-action-button>
        </form>
    @elseif ($investorUsers->isEmpty())
        <p class="text-sm text-foreground-muted">{{ __('Every active investor is already tagged on this pool.') }}</p>
    @endif
</div>

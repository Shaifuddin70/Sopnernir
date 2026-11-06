@props([
    'action',
    'confirm' => __('Reverse this withdrawal? The withdrawn amount will be added back to available profit.'),
])

<form method="post" action="{{ $action }}" class="inline" onsubmit="return confirm(@js($confirm));">
    @csrf
    @method('delete')
    <x-action-button variant="danger" class="text-sm">{{ __('Reverse') }}</x-action-button>
</form>

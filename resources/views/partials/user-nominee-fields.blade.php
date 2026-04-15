@props(['user' => null])

{{-- Same two-column layout as admin users create / edit --}}
<div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-start lg:gap-12">
    <div class="lg:border-r lg:border-gray-200 lg:pe-8">
        @include('partials.user-account-fields', ['user' => $user])
    </div>
    <div>
        @include('partials.user-nominee-fields-section', ['user' => $user])
    </div>
</div>

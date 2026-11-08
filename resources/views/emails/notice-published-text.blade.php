{{ config('app.name') }}

{{ $noticeTitle }}

{{ __('Hello :name,', ['name' => $userName]) }}

{{ __('A new notice has been posted on :app.', ['app' => $appName]) }}

@if ($excerpt)
{{ __('Summary') }}: {{ $excerpt }}
@endif

@if ($publishedAt)
{{ __('Published') }}: {{ $publishedAt }}
@endif
@if ($attachmentCount > 0)
{{ __('Attachments') }}: {{ $attachmentCount }}
@endif

{{ __('View notice') }}: {{ $actionUrl }}

{{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $appName]) }}

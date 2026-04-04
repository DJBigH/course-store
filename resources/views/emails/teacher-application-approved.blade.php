<p>{{ __('teacher::mail.approved.greeting', ['name' => $application->full_name]) }}</p>
<p>{{ __('teacher::mail.approved.events.' . $packageAction) }}</p>

@if ($application->package)
    <p>
        {{ __('teacher::mail.approved.labels.package') }}
        <strong>{{ $application->package->name_locale ?: $application->package->name }}</strong>
    </p>
@endif

@if ($packageAction === 'queued' && $application->activates_at)
    <p>
        {{ __('teacher::mail.approved.labels.starts_at') }}
        <strong>{{ $application->activates_at->format('d/m/Y') }}</strong>
    </p>
@elseif (in_array($packageAction, ['activated', 'extended'], true) && $application->package_started_at)
    <p>
        {{ __('teacher::mail.approved.labels.starts_at') }}
        <strong>{{ $application->package_started_at->format('d/m/Y') }}</strong>
    </p>
@endif

@if ($application->package_expires_at)
    <p>
        {{ __('teacher::mail.approved.labels.expires_at') }}
        <strong>{{ $application->package_expires_at->format('d/m/Y') }}</strong>
    </p>
@endif

@if ($passwordSetupUrl)
    <p>{{ __('teacher::mail.approved.new_account_intro') }}</p>
    <p>{{ __('teacher::mail.approved.login_email') }} <strong>{{ $application->email }}</strong></p>
    <p><a href="{{ $passwordSetupUrl }}">{{ __('teacher::mail.approved.setup_password') }}</a></p>
    <p>{{ __('teacher::mail.approved.setup_password_hint') }}</p>
@else
    <p>{{ __('teacher::mail.approved.existing_account_intro') }}</p>
    <p>{{ __('teacher::mail.approved.login_email') }} <strong>{{ $application->email }}</strong></p>
    <p>{{ __('teacher::mail.approved.existing_account_hint') }}</p>
@endif

@if (!empty($application->admin_note))
    <p>{{ __('teacher::mail.approved.admin_note') }} {{ $application->admin_note }}</p>
@endif

<p>{{ __('teacher::mail.approved.closing') }}</p>
<p>{{ __('teacher::mail.approved.signature') }}<br>{{ __('teacher::mail.approved.brand') }}</p>

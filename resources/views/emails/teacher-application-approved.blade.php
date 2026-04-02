<p>{{ __('teacher::mail.approved.greeting', ['name' => $application->full_name]) }}</p>
<p>{{ __('teacher::mail.approved.intro') }}</p>

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

<tr>
    <td
        style="padding:{{ $mailTokens['footer_padding'] }};background:{{ $mailTheme['surface'] }};border-top:1px solid {{ $mailTheme['line'] }};">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="font-size:12px;color:{{ $mailTheme['muted'] }};line-height:1.5;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}.
                    {{ __('auth::clients/email.common.all_rights_reserved') }}
                </td>
                <td align="right" style="font-size:12px;color:{{ $mailTheme['muted_soft'] }};">
                    {{ __('auth::clients/email.common.automated_email') }}
                </td>
            </tr>
        </table>
    </td>
</tr>

@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration:none;">
<span style="display:inline-block;padding:10px 18px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">
{{ __('auth::clients/email.common.system_notice') }}
</span>
<span style="display:block;margin-top:12px;color:#0f172a;font-size:26px;font-weight:800;letter-spacing:-0.02em;">
{{ trim($slot) === 'Laravel' ? config('app.name') : $slot }}
</span>
</a>
</td>
</tr>

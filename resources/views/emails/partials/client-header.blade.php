<tr>
    <td
        style="padding:{{ $mailTokens['header_padding'] }};background:linear-gradient(135deg,{{ $mailTheme['primary_dark'] }},{{ $mailTheme['primary'] }});">
        <div style="font-size:13px;color:{{ $mailTheme['primary_soft'] }};">
            {{ $eyebrow }}
        </div>
        <div style="font-size:20px;font-weight:800;color:#ffffff;margin-top:4px;">
            {{ $title }}
        </div>
        @if (!empty($subtitle))
            <div style="margin-top:10px;font-size:13px;color:{{ $mailTheme['accent_soft'] }};">
                {!! $subtitle !!}
            </div>
        @endif
    </td>
</tr>

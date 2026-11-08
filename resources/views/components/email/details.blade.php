@props([
    'title',
    'rows' => [],
])

@if (count($rows))
    <div class="em-details-title" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a1c1e;font-size:20px;line-height:1.3;font-weight:700;margin:0 0 6px;">{{ $title }}</div>
    <div class="em-accent" style="width:42px;height:3px;background-color:#1976d2;margin:0 0 16px;border-radius:2px;line-height:3px;font-size:0;">&nbsp;</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="em-details" style="margin:0 0 28px;border:1px solid #e0e6ee;border-radius:16px;overflow:hidden;">
        @foreach ($rows as $index => $row)
            @php($last = $index === count($rows) - 1)
            <tr>
                <td class="em-details-row em-details-label" style="padding:14px 16px;border-bottom:{{ $last ? '0' : '1px solid #e0e6ee' }};font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#5f6368;font-size:14px;line-height:1.4;">
                    {{ $row['label'] }}
                </td>
                <td align="right" class="em-details-row em-details-value" style="padding:14px 16px;border-bottom:{{ $last ? '0' : '1px solid #e0e6ee' }};font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a1c1e;font-size:14px;line-height:1.4;font-weight:700;">
                    {{ $row['value'] }}
                </td>
            </tr>
        @endforeach
    </table>
@endif

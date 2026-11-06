@props([
    'title',
    'rows' => [],
])

@if (count($rows))
    <div style="font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:20px;line-height:1.3;font-weight:700;margin:0 0 6px;">{{ $title }}</div>
    <div style="width:42px;height:3px;background-color:#C6A04A;margin:0 0 16px;border-radius:2px;line-height:3px;font-size:0;">&nbsp;</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;border:1px solid #E6EAF0;border-radius:12px;overflow:hidden;">
        @foreach ($rows as $index => $row)
            <tr>
                <td style="padding:14px 16px;border-bottom:{{ $index < count($rows) - 1 ? '1px solid #E6EAF0' : '0' }};font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:14px;line-height:1.4;">
                    {{ $row['label'] }}
                </td>
                <td align="right" style="padding:14px 16px;border-bottom:{{ $index < count($rows) - 1 ? '1px solid #E6EAF0' : '0' }};font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:14px;line-height:1.4;font-weight:700;">
                    {{ $row['value'] }}
                </td>
            </tr>
        @endforeach
    </table>
@endif

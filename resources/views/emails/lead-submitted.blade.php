<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>

<body style="margin:0; padding:0; background:#F4EEE0; font-family:Arial, Helvetica, sans-serif; color:#1F2A24;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4EEE0; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:560px; background:#FFFDF8; border:1px solid #E6DECB; border-radius:16px;">
                    <tr>
                        <td style="padding:28px 32px 20px; font-size:15px; line-height:1.6;">
                            @foreach ($paragraphs as $paragraph)
                                <p style="margin:0 0 16px;">{!! nl2br(e($paragraph)) !!}</p>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>

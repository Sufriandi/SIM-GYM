{{-- resources/views/emails/reset-password.blade.php --}}
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - BETA GYM</title>
</head>
<body style="margin:0; padding:0; background-color:#0b0f14; font-family: Arial, Helvetica, sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0f14; padding: 40px 16px;">
<tr>
<td align="center">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 480px; background-color:#151a21; border-radius: 16px; overflow: hidden; border: 1px solid #2a2f38;">

        {{-- HEADER --}}
        <tr>
            <td style="background: linear-gradient(135deg, #1a1a1a 0%, #0b0f14 100%); padding: 36px 32px 28px 32px; text-align:center; border-bottom: 3px solid #c8a870;">
                <div style="font-size: 22px; font-weight: 800; letter-spacing: 2px; color: #ffffff; text-transform: uppercase;">
                    BETA<span style="color:#c8a870;">GYM</span>
                </div>
                <div style="font-size: 11px; letter-spacing: 3px; color: #8a8f98; text-transform: uppercase; margin-top: 6px;">
                    Build a Better You
                </div>
            </td>
        </tr>

        {{-- BODY --}}
        <tr>
            <td style="padding: 36px 32px 8px 32px;">
                <div style="width:48px; height:48px; border-radius:50%; background-color:rgba(200,168,112,0.12); text-align:center; line-height:48px; margin-bottom:20px;">
                    <span style="font-size:22px;">&#128274;</span>
                </div>
                <h1 style="margin:0 0 12px 0; font-size:20px; color:#ffffff; font-weight:700;">
                    Permintaan Reset Password
                </h1>
                <p style="margin:0 0 20px 0; font-size:14px; line-height:1.7; color:#a8adb5;">
                    Halo{{ isset($notifiable) && $notifiable->name ? ', ' . $notifiable->name : '' }}! Kami menerima permintaan untuk mengatur ulang password akun BETA GYM kamu. Klik tombol di bawah untuk melanjutkan.
                </p>
            </td>
        </tr>

        {{-- BUTTON --}}
        <tr>
            <td style="padding: 8px 32px 28px 32px; text-align:center;">
                <a href="{{ $url }}"
                   style="display:inline-block; background-color:#c8a870; color:#0b0f14; font-size:14px; font-weight:700; text-decoration:none; padding:14px 36px; border-radius:10px; letter-spacing:0.5px;">
                    Reset Password Sekarang
                </a>
            </td>
        </tr>

        {{-- INFO NOTE --}}
        <tr>
            <td style="padding: 0 32px 28px 32px;">
                <div style="background-color:#1c222b; border: 1px solid #2a2f38; border-radius:10px; padding:16px 18px;">
                    <p style="margin:0 0 8px 0; font-size:12.5px; line-height:1.6; color:#8a8f98;">
                        &#9200; Link ini berlaku selama <strong style="color:#c8a870;">60 menit</strong> sejak email ini dikirim.
                    </p>
                    <p style="margin:0; font-size:12.5px; line-height:1.6; color:#8a8f98;">
                        Kalau kamu tidak merasa meminta reset password, abaikan saja email ini — password kamu tetap aman, tidak ada perubahan apapun.
                    </p>
                </div>
            </td>
        </tr>

        {{-- FALLBACK LINK --}}
        <tr>
            <td style="padding: 0 32px 32px 32px;">
                <p style="margin:0 0 6px 0; font-size:11.5px; color:#6b7078;">
                    Tombol tidak berfungsi? Salin tautan berikut ke browser kamu:
                </p>
                <p style="margin:0; font-size:11px; word-break:break-all;">
                    <a href="{{ $url }}" style="color:#c8a870; text-decoration:underline;">{{ $url }}</a>
                </p>
            </td>
        </tr>

        {{-- DIVIDER --}}
        <tr>
            <td style="padding: 0 32px;">
                <div style="border-top: 1px solid #2a2f38;"></div>
            </td>
        </tr>

        {{-- FOOTER --}}
        <tr>
            <td style="padding: 20px 32px 32px 32px; text-align:center;">
                <p style="margin:0; font-size:11px; color:#5a5f66;">
                    &copy; {{ date('Y') }} BETA GYM. Semua hak dilindungi.
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>

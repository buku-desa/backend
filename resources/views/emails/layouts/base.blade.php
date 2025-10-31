<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Notifikasi LDBD')</title>
</head>

<body
    style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 30px; color: #333;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0"
        style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <tr>
            <td style="padding: 30px;">
                @yield('content')

                <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">

                <p style="font-size: 12px; color: #9ca3af; text-align: center;">
                    Sistem Buku Lembaran & Berita Desa (LDBD)<br>
                    Email ini dikirim otomatis oleh sistem.
                </p>
            </td>
        </tr>
    </table>
</body>

</html>

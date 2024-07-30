<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Reset Password</title>
</head>

<body>
    <h1>Instruksi Reset Password</h1>
    <p>Hai {{ $nama }},</p>
    <p>Anda telah meminta untuk mereset password akun Anda. Silakan klik link di bawah ini untuk mereset password Anda dengan menyalin email ini {{ $email }}:</p>
    <p><a href="{{ url('https://adminattendancedistro.netlify.app/pengaturan/akun/ubah-kata-sandi') }}">Reset Password</a></p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
    <p>Terima kasih,</p>
    <p>Tim Support</p>
</body>

</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Pemindahan Unit Kerja dan Jabatan Karyawan</title>
</head>
<body>
    <h1>Pemberitahuan Pemindahan Unit Kerja dan Jabatan Karyawan</h1>
    <p>Yth. Bapak/Ibu,</p>

    <p>Dengan ini kami memberitahukan bahwa karyawan dengan informasi berikut ini akan dipindahkan ke unit kerja dan jabatan baru:</p>

    <table>
        <tr>
            <th>Nama:</th>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <th>Email:</th>
            <td>{{ $email }}</td>
        </tr>
        <tr>
            <th>Unit Kerja Asal:</th>
            <td>{{ $unit_kerja_asals }}</td>
        </tr>
        <tr>
            <th>Unit Kerja Tujuan:</th>
            <td>{{ $unit_kerja_tujuans }}</td>
        </tr>
        <tr>
            <th>Jabatan Asal:</th>
            <td>{{ $jabatan_asals }}</td>
        </tr>
        <tr>
            <th>Jabatan Tujuan:</th>
            <td>{{ $jabatan_tujuans }}</td>
        </tr>
        <tr>
            <th>Alasan:</th>
            <td>{{ $alasan }}</td>
        </tr>
        <tr>
            <th>Tanggal Mulai:</th>
            <td>{{ $tanggal_mulai }}</td>
        </tr>
    </table>

    <p>Demikian pemberitahuan ini kami sampaikan. Terima kasih atas perhatian dan kerjasamanya.</p>

    <p>Salam,</p>
    <p>Tim HRD</p>
</body>
</html>

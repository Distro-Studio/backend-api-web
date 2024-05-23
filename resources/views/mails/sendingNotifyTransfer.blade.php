<!DOCTYPE html>
<html>

<head>
	<title>Transfer Karyawan</title>
</head>

<body>
	<h1>Transfer Karyawan Berhasil</h1>
	<p>Nama: {{ $nama }}</p>
	<p>Email: {{ $email }}</p>
	<p>Unit Kerja Awal: {{ $unitKerja_from }}</p>
	<p>Unit Kerja Tujuan: {{ $unitKerja_to }}</p>
	<p>Jabatan Awal: {{ $jabatan_from }}</p>
	<p>Jabatan Tujuan: {{ $jabatan_to }}</p>
	<p>Alasan: {{ $alasan }}</p>
	<p>Waktu Transfer: {{ $tanggal }}</p>
</body>

</html>
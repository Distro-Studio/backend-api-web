<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sertifikat</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			text-align: center;
			padding: 50px;
		}

		.certificate {
			border: 5px solid #333;
			padding: 20px;
		}

		.certificate h1 {
			font-size: 50px;
			margin-bottom: 20px;
		}

		.certificate p {
			font-size: 20px;
		}
	</style>
</head>

<body>
	<div class="certificate">
		<h1>Sertifikat Keikutsertaan</h1>
		<p>Ini menyatakan bahwa</p>
		<h2>{{ $user->nama }}</h2>
		<p>telah berpartisipasi dalam Diklat</p>
		<h3>{{ $diklat->nama }}</h3>
		<p>Pada tanggal {{ \Carbon\Carbon::parse($diklat->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
	</div>
</body>

</html>
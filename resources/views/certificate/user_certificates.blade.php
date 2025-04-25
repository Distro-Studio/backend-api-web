<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<title>Sertifikat</title>
	<style>
		body {
			font-family: "Times New Roman", serif;
			padding: 60px;
			text-align: center;
		}

		.certificate {
			border: 6px double #000;
			padding: 40px 60px;
			width: 1000px;
			margin: 0 auto;
		}

		.logo {
			float: left;
			text-align: left;
		}

		.logo img {
			width: 100px;
		}

		.title {
			font-size: 36px;
			font-weight: bold;
			text-decoration: underline;
			margin-bottom: 20px;
		}

		.subtitle {
			font-size: 20px;
			margin: 10px 0;
		}

		.participant-name {
			font-size: 26px;
			font-weight: bold;
			margin: 20px 0;
		}

		.diklat-title {
			font-size: 22px;
			font-weight: bold;
			text-transform: uppercase;
			margin: 30px 0;
		}

		.date {
			margin-top: 40px;
			font-size: 16px;
		}

		.signatures {
			display: flex;
			justify-content: space-between;
			margin-top: 60px;
			font-size: 16px;
		}

		.signatures div {
			text-align: center;
		}

		.signature-name {
			margin-top: 80px;
			font-weight: bold;
		}

		.signature-title {
			margin-top: 5px;
			font-size: 14px;
		}
	</style>
</head>

<body>
	<div class="certificate">

		<div class="title">Sertifikat</div>

		<div class="subtitle">Diberikan Kepada :</div>
		<div class="participant-name">{{ $user->nama }}</div>
		<div class="subtitle">Atas Partisipasinya Sebagai :</div>
		<div class="subtitle" style="font-weight: bold;">PESERTA</div>

		<div class="diklat-title">{{ strtoupper($diklat->nama) }}</div>

		<div class="date">RS Kasih Ibu Surakarta, {{ \Carbon\Carbon::parse($diklat->tgl_mulai)->locale('id')->isoFormat('D MMMM YYYY') }}</div>

		<div class="signatures">
			<div>
				<div class="signature-name">Dr. Ndarumuti Pangesti, SpPD-KEMD</div>
				<div class="signature-title">Direktur RS Kasih Ibu</div>
			</div>
			<div>
				<div class="signature-name">Dr. Ranissa Eka Sukmaningtyas</div>
				<div class="signature-title">Diklat RS Kasih Ibu</div>
			</div>
		</div>
	</div>
</body>

</html>
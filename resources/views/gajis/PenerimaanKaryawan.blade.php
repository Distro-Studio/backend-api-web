<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekap Penerimaan Gaji Karyawan</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 6pt;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 10px;
		}

		th,
		td {
			border: 1px solid #000;
			padding: 5px;
			text-align: center;
		}

		th {
			background-color: #f2f2f2;
		}

		.total-row td {
			font-weight: bold;
			text-align: center;
		}
	</style>
</head>

<body>
	<h1 style="text-align: center;">Rekap Penerimaan Gaji per Karyawan</h1>
	<h2 style="text-align: center;">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y') }}</h2>
	@if(isset($nama_unit))
        <h3 style="text-align: center;">Unit Kerja: {{ $nama_unit }}</h3>
    @endif

	@foreach ($dataChunks as $index => $dataChunk)
	<table>
		<thead>
			<tr>
				<th>No</th>
				<th>NIK</th>
				<th>Nama Karyawan</th>
				<th>Status Karyawan</th>
				<th>Unit Kerja</th>
				<th>Gaji Pokok</th>
				<th>Tunjangan Jabatan</th>
				<th>Tunjangan Fungsional</th>
				<th>Tunjangan Khusus</th>
				<th>Tunjangan Lainnya</th>
				<th>Uang Lembur</th>
				<th>Uang Makan</th>
				<th>Reward BOR</th>
				<th>Reward Absensi</th>
				<th>Jumlah Penghasilan</th>
				<th>Jumlah Premi</th>
				<th>PPh21</th>
				<th>Penambah Gaji</th>
				<th>Pengurang Gaji</th>
				<th>Take Home Pay</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($dataChunk as $row)
			<tr>
				<td>{{ $row['no'] }}</td>
				<td>{{ $row['nik'] }}</td>
				<td>{{ $row['nama_karyawan'] }}</td>
				<td>{{ $row['status_karyawan'] }}</td>
				<td>{{ $row['unit_kerja'] }}</td>
				<td>{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['tunjangan_jabatan'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['tunjangan_fungsional'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['tunjangan_khusus'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['tunjangan_lainnya'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['uang_lembur'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['uang_makan'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['bonus_bor'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['bonus_presensi'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['jumlah_penghasilan'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['jumlah_premi'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['pph21'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['penambah_gaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['pengurang_gaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['gaji_diterima'], 0, ',', '.') }}</td>
			</tr>
			@endforeach

			<!-- Total Row -->
			@if ($loop->last && $index == $dataChunks->count() - 1)
			<tr class="total-row">
				<td colspan="5">Total</td>
				<td>{{ number_format($totals['gaji_pokok'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['tunjangan_jabatan'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['tunjangan_fungsional'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['tunjangan_khusus'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['tunjangan_lainnya'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['uang_lembur'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['uang_makan'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['bonus_bor'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['bonus_presensi'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['jumlah_penghasilan'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['jumlah_premi'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['pph21'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['penambah_gaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['pengurang_gaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['gaji_diterima'], 0, ',', '.') }}</td>
			</tr>
			@endif
		</tbody>
	</table>

	<!-- Add Page Break -->
	@if (!$loop->last)
	<div style="page-break-after: always;"></div>
	@endif
	@endforeach
</body>

</html>
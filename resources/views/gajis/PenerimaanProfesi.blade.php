<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekap Penerimaan Gaji Profesi</title>
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
	<h1 style="text-align: center;">Rekap Penerimaan Gaji per Profesi</h1>
	<h2 style="text-align: center;">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y') }}</h2>
	@if(isset($nama_profesi))
        <h3 style="text-align: center;">Profesi: {{ $nama_profesi }}</h3>
    @endif

	@foreach ($dataChunks as $index => $dataChunk)
	<table>
		<thead>
			<tr>
				<th>No</th>
				<th>Nama Kompetensi</th>
				<th>Jumlah Karyawan Kompetensi</th>
				<th>Jumlah Karyawan Digaji</th>
				<th>Take Home Pay</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($dataChunk as $row)
			<tr>
				<td>{{ $row['No'] }}</td>
				<td>{{ $row['Nama Kompetensi'] }}</td>
				<td>{{ number_format($row['Jumlah Karyawan Kompetensi'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Jumlah Karyawan Digaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Take Home Pay'], 0, ',', '.') }}</td>
			</tr>
			@endforeach

			<!-- Total Row -->
			@if ($loop->last && $index == $dataChunks->count() - 1)
			<tr class="total-row">
				<td colspan="2">Total</td>
				<td>{{ number_format($totals['Jumlah Karyawan Kompetensi'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Jumlah Karyawan Digaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Take Home Pay'], 0, ',', '.') }}</td>
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
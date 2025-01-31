<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekap Penerimaan Gaji Unit Kerja</title>
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

		h1,
		h2 {
			text-align: center;
		}

		h1 {
			font-size: 12pt;
		}

		h2 {
			font-size: 10pt;
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
	<h1>Rekap Pengurang Gaji Unit Kerja</h1>
	<h2>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y') }}</h2>

	@foreach ($dataChunks as $index => $dataChunk)
	<table>
		<thead>
			<tr>
				<th>No</th>
				<th>Nama Unit</th>
				<th>Jumlah Karyawan Unit</th>
				<th>Jumlah Karyawan Digaji</th>
				<th>PPh21</th>
				<th>Pot. Koperasi</th>
				<th>Pot. Obat</th>
				@foreach ($premis as $premiKey => $premiName)
				<th>{{ $premiName }}</th>
				@endforeach
				<th>Potongan Lainnya</th>
				<th>Jumlah Potongan</th>
				<th>Take Home Pay</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($dataChunk as $row)
			<tr>
				<td>{{ $row['No'] }}</td>
				<td>{{ $row['Nama Unit'] }}</td>
				<td>{{ $row['Jumlah Karyawan Unit'] }}</td>
				<td>{{ $row['Jumlah Karyawan Digaji'] }}</td>
				<td>{{ number_format($row['PPh21'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Pot. Koperasi'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Pot. Obat'], 0, ',', '.') }}</td>
				@foreach ($premis as $premiKey => $premiName)
				<td>{{ number_format($row["premi_{$premiKey}"] ?? 0, 0, ',', '.') }}</td>
				@endforeach
				<td>{{ number_format($row['Potongan Lainnya'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Jumlah Potongan'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Take Home Pay'], 0, ',', '.') }}</td>
			</tr>
			@endforeach

			@if ($loop->last && $index == $dataChunks->count() - 1)
			<tr class="total-row">
				<td colspan="2">Total</td>
				<td>{{ number_format($totals['Jumlah Karyawan Unit'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Jumlah Karyawan Digaji'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['PPh21'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Pot. Koperasi'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Pot. Obat'], 0, ',', '.') }}</td>
				@foreach ($premis as $premiKey => $premiName)
				<td>{{ number_format($totals["premi_{$premiKey}"] ?? 0, 0, ',', '.') }}</td>
				@endforeach
				<td>{{ number_format($totals['Potongan Lainnya'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Jumlah Potongan'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Take Home Pay'], 0, ',', '.') }}</td>
			</tr>
			@endif
		</tbody>
	</table>

	@if (!$loop->last)
	<div style="page-break-after: always;"></div>
	@endif
	@endforeach
</body>

</html>
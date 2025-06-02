<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekap Laporan Gaji Pengurang</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 6pt;
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
	<h1>Rekap Laporan Gaji Pengurang</h1>
	<h2>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y') }}</h2>

	@foreach ($dataChunks as $index => $dataChunk)
	<table>
		<thead>
			<tr>
				<th>No</th>
				<th>Unit Kerja</th>
				<th>Jumlah Karyawan</th>
				<th>Gaji Bruto</th>
				<th>Tambahan Lain</th>
				<th>Total Penghasilan</th>
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
			<!-- <div style="
				white-space: pre-wrap;
				word-wrap: break-word;
				border: 1px solid #ddd;
				background-color: #f9f9f9;
				font-family: monospace;
				font-size: 10pt;
			">
				{{ json_encode($row) }}
			</div> -->
			<tr>
				<td>{{ $row['No'] }}</td>
				<td>{{ $row['Unit Kerja'] }}</td>
				<td>{{ $row['Jumlah Karyawan'] }}</td>
				<td>{{ number_format($row['Gaji Bruto'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Tambahan Lain'], 0, ',', '.') }}</td>
				<td>{{ number_format($row['Total Penghasilan'], 0, ',', '.') }}</td>
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
				<td>{{ number_format($totals['Jumlah Karyawan'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Gaji Bruto'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Tambahan Lain'], 0, ',', '.') }}</td>
				<td>{{ number_format($totals['Total Penghasilan'], 0, ',', '.') }}</td>
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
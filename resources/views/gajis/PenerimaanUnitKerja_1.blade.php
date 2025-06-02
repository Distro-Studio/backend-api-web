<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekapitulasi Kelompok</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 5pt;
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
			margin-left: -20px;
		}

		th,
		td {
			border: 1px solid #000;
			padding: 3px;
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
	<h1>Rekapitulasi Kelompok</h1>
	<h2>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', "{$years[0]}-{$months[0]}")->locale('id')->isoFormat('MMMM Y') }}</h2>

	@foreach ($dataChunks as $index => $dataChunk)
	<table>
		<thead>
			<tr>
				<th>No</th>
				<th>Kategori</th>
				<th>Jumlah Karyawan</th>
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
				<td>{{ $row['Kategori'] }}</td>
				<td>{{ $row['Jumlah Karyawan'] }}</td>
				<td>{{ number_format($row['Take Home Pay'], 0, ',', '.') }}</td>
			</tr>
			@endforeach

			@if ($loop->last && $index == $dataChunks->count() - 1)
			<tr class="total-row">
				<td colspan="2">Total</td>
				<td>{{ number_format($totals['Jumlah Karyawan'], 0, ',', '.') }}</td>
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
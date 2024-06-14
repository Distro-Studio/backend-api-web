<?php

namespace App\Http\Controllers\Dashboard\Presensi;

use App\Exports\Presensi\PresensiExport;
use Carbon\Carbon;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Excel_Import\ImportPresensiRequest;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\Dashboard\Presensi\PresensiResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Imports\Presensi\PresensiImport;
use App\Models\Jadwal;
use App\Models\Shift;
use App\Models\User;

class PresensiController extends Controller
{
	/* ============================= For Dropdown ============================= */
	public function getAllPresensi()
	{
		if (!Gate::allows('view presensiKaryawan')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		$dataPresensi = Presensi::with('users', 'data_karyawans.unit_kerjas', 'jadwals')->get();
		return response()->json([
			'status' => Response::HTTP_OK,
			'message' => 'Retrieving all presensi for dropdown',
			'data' => $dataPresensi
		], Response::HTTP_OK);
	}
	/* ============================= For Dropdown ============================= */

	public function index(Request $request)
	{
		if (!Gate::allows('view presensiKaryawan')) {
			return response()->json(['status' => Response::HTTP_FORBIDDEN, 'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.'], Response::HTTP_FORBIDDEN);
		}

		$presensi = Presensi::query()->with(['users.data_karyawans.unit_kerjas', 'jadwals.shifts']);

		// Filter
		if ($request->has('kategori')) {
			if (is_array($request->kategori)) {
				$presensi->whereIn('kategori', $request->kategori);
			} else {
				$presensi->where('kategori', $request->kategori);
			}
		}

		if ($request->has('status_karyawan')) {
			$statuskaryawan = $request->status_karyawan;
			$presensi->whereHas('users.data_karyawans', function ($query) use ($statuskaryawan) {
				if (is_array($statuskaryawan)) {
					$query->whereIn('status_karyawan', $statuskaryawan);
				} else {
					$query->where('status_karyawan', '=', $statuskaryawan);
				}
			});
		}

		if ($request->has('nama_unit')) {
			$namaUnitKerja = $request->nama_unit;
			$presensi->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
				if (is_array($namaUnitKerja)) {
					$query->whereIn('nama_unit', $namaUnitKerja);
				} else {
					$query->where('nama_unit', '=', $namaUnitKerja);
				}
			});
		}

		if ($request->has('hari_masuk')) {
			$tanggal = Carbon::parse($request->hari_masuk)->format('Y-m-d');
			$presensi->whereDate('jam_masuk', $tanggal);
		}

		// Search
		if ($request->has('search')) {
			$searchTerm = '%' . $request->search . '%';
			$presensi->where(function ($query) use ($searchTerm) {
				$query->whereHas('users', function ($query) use ($searchTerm) {
					$query->where('nama', 'like', $searchTerm);
				})->orWhereHas('users.data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
					$query->where('nama_unit', 'like', $searchTerm);
				})->orWhereHas('jadwals.shifts', function ($query) use ($searchTerm) {
					$query->where('nama', 'like', $searchTerm);
				});
			});
		}

		$dataPresensi = $presensi->paginate(10);

		if ($dataPresensi->isEmpty()) {
			return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => 'Data presensi tidak ditemukan.'], Response::HTTP_NOT_FOUND);
		}

		// Format data untuk output
		$formattedData = $dataPresensi->items();
		$formattedData = array_map(function ($presensi) {
			$jamMasuk = Carbon::parse($presensi->jam_masuk);
			$jamKeluar = Carbon::parse($presensi->jam_keluar);
			$durasi = $jamKeluar->diffInSeconds($jamMasuk);

			return [
				'id' => $presensi->id,
				'user' => $presensi->users ?? null,
				'unit_kerja' => $presensi->users && $presensi->users->data_karyawans ? [
					'id' => $presensi->users->data_karyawans->unit_kerjas->id,
					'nama_unit' => $presensi->users->data_karyawans->unit_kerjas->nama_unit,
					'jenis_karyawan' => $presensi->users->data_karyawans->unit_kerjas->jenis_karyawan,
					'created_at' => $presensi->users->data_karyawans->unit_kerjas->created_at,
					'updated_at' => $presensi->users->data_karyawans->unit_kerjas->updated_at,
				] : null,
				'jadwal' => $presensi->jadwals->shifts ?? null,
				'jam_masuk' => $presensi->jam_masuk,
				'jam_keluar' => $presensi->jam_keluar,
				'created_at' => $presensi->created_at,
				'updated_at' => $presensi->updated_at
			];
		}, $formattedData);

		$paginationData = [
			'links' => [
				'first' => $dataPresensi->url(1),
				'last' => $dataPresensi->url($dataPresensi->lastPage()),
				'prev' => $dataPresensi->previousPageUrl(),
				'next' => $dataPresensi->nextPageUrl(),
			],
			'meta' => [
				'current_page' => $dataPresensi->currentPage(),
				'last_page' => $dataPresensi->lastPage(),
				'per_page' => $dataPresensi->perPage(),
				'total' => $dataPresensi->total(),
			]
		];

		return response()->json([
			'status' => Response::HTTP_OK,
			'message' => 'Data presensi berhasil ditampilkan.',
			'data' => $formattedData,
			'pagination' => $paginationData
		], Response::HTTP_OK);
	}

	public function show($id)
	{
		if (!Gate::allows('view presensiKaryawan')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		$presensi = Presensi::with(['users.data_karyawans.unit_kerjas', 'jadwals.shifts'])->find($id);

		if (!$presensi) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
		}

		$formattedData = [
			'id' => $presensi->id,
			'user' => $presensi->users ?? null,
			'unit_kerja' => $presensi->users && $presensi->users->data_karyawans ? [
				'id' => $presensi->users->data_karyawans->unit_kerjas->id,
				'nama_unit' => $presensi->users->data_karyawans->unit_kerjas->nama_unit,
				'jenis_karyawan' => $presensi->users->data_karyawans->unit_kerjas->jenis_karyawan,
				'created_at' => $presensi->users->data_karyawans->unit_kerjas->created_at,
				'updated_at' => $presensi->users->data_karyawans->unit_kerjas->updated_at,
			] : null,
			'jadwal' => $presensi->jadwals->shifts ?? null,
			'jam_masuk' => $presensi->jam_masuk,
			'jam_keluar' => $presensi->jam_keluar,
			'durasi' => $presensi->durasi,
			'lat' => $presensi->lat,
			'long' => $presensi->long,
			'foto_masuk' => $presensi->foto_masuk,
			'foto_keluar' => $presensi->foto_keluar,
			'presensi' => $presensi->presensi,
			'kategori' => $presensi->kategori,
			'created_at' => $presensi->created_at,
			'updated_at' => $presensi->updated_at
		];

		return response()->json([
			'status' => Response::HTTP_OK,
			'message' => 'Detail presensi berhasil ditampilkan.',
			'data' => $formattedData,
		], Response::HTTP_OK);
	}

	public function exportPresensi(Request $request)
	{
		if (!Gate::allows('export presensiKaryawan')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		try {
			return Excel::download(new PresensiExport(), 'presensi-karyawan.xls');
		} catch (\Exception $e) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
		} catch (\Error $e) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
		}

		return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di download.'), Response::HTTP_OK);
	}

	public function importPresensi(ImportPresensiRequest $request)
	{
		if (!Gate::allows('import presensiKaryawan')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		$file = $request->validated();

		try {
			Excel::import(new PresensiImport, $file['presensi_file']);
		} catch (\Exception $e) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
		}

		return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di import kedalam tabel.'), Response::HTTP_OK);
	}

	public function calculatedPresensi()
	{
		if (!Gate::allows('view presensiKaryawan')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		$countTepatWaktu = Presensi::where('kategori', 'Tepat Waktu')->count();
		$countHadir = Presensi::where('kategori', 'Hadir')->count();
		$countTerlambat = Presensi::where('kategori', 'Terlambat')->count();
		$countAbsen = Presensi::where('kategori', 'Absen')->count();
		$countIzin = Presensi::where('kategori', 'Izin')->count();
		$countInvalid = Presensi::where('kategori', 'Invalid')->count();
		$countLibur = Presensi::where('kategori', 'Libur')->count();
		$countCuti = Presensi::where('kategori', 'Cuti')->count();
		$totalPresensi = $countTepatWaktu + $countHadir + $countTerlambat + $countAbsen + $countIzin + $countInvalid + $countLibur + $countCuti;

		return response()->json([
			'status' => Response::HTTP_OK,
			'message' => 'Perhitungan presensi karyawan.',
			'data' => [
				'total_tepat_waktu' => $countTepatWaktu,
				'total_hadir' => $countHadir,
				'total_terlambat' => $countTerlambat,
				'total_absen' => $countAbsen,
				'total_izin' => $countIzin,
				'total_invalid' => $countInvalid,
				'total_libur' => $countLibur,
				'total_cuti' => $countCuti,
				'total_semua_presensi' => $totalPresensi
			],
		], Response::HTTP_OK);
	}
}

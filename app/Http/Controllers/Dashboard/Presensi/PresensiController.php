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
		if (!Gate::allows('view presensi')) {
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
		if (!Gate::allows('view presensi')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		$presensi = Presensi::query();

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

			$presensi->with('data_karyawans:user_id,status_karyawan')
				->whereHas('data_karyawans', function ($query) use ($statuskaryawan) {
					if (is_array($statuskaryawan)) {
						$query->whereIn('status_karyawan', $statuskaryawan);
					} else {
						$query->where('status_karyawan', '=', $statuskaryawan);
					}
				});
		}

		if ($request->has('nama_unit')) {
			$namaUnitKerja = $request->nama_unit;

			$presensi->whereHas('data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
				if (is_array($namaUnitKerja)) {
					$query->whereIn('nama_unit', $namaUnitKerja);
				} else {
					$query->where('nama_unit', '=', $namaUnitKerja);
				}
			});
		}

		if ($request->has('jam_masuk')) {
			$tanggal = Carbon::parse($request->jam_masuk)->format('Y-m-d');

			$presensi->whereDate('jam_masuk', $tanggal);
		}

		// Search
		if ($request->has('search')) {
			$presensi = $presensi->where(function ($query) use ($request) {
				$searchTerm = '%' . $request->search . '%';

				$query->whereHas('users', function ($query) use ($searchTerm) {
					$query->where('nama', 'like', $searchTerm);
				});
				$query->orWhereHas('data_karyawans.unit_kerjas', function ($query) use ($searchTerm) {
					$query->where('nama_unit', 'like', $searchTerm);
				});
				$query->orWhereHas('jadwals.shifts', function ($query) use ($searchTerm) {
					$query->where('nama', 'like', $searchTerm);
				});
			});
		}

		$dataPresensi = $presensi->paginate(10);
		if ($dataPresensi->isEmpty()) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
		}

		return response()->json(new PresensiResource(Response::HTTP_OK, 'Data presensi berhasil ditampilkan.', $dataPresensi), Response::HTTP_OK);
	}

	public function show(Presensi $data_presensi)
	{
		if (!Gate::allows('view presensi')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		if (!$data_presensi) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
		}

		return response()->json(new PresensiResource(Response::HTTP_OK, "Data presensi dari {$data_presensi->users->nama} berhasil di tampilkan.", $data_presensi), Response::HTTP_OK);
	}

	public function exportPresensi(Request $request)
	{
		if (!Gate::allows('export presensi')) {
			return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
		}

		try {
			$ids = $request->input('ids', []);
			return Excel::download(new PresensiExport($ids), 'presensi-karyawans.xls');
		} catch (\Exception $e) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
		} catch (\Error $e) {
			return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
		}

		return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data presensi karyawan berhasil di download.'), Response::HTTP_OK);
	}

	public function importPresensi(ImportPresensiRequest $request)
	{
		if (!Gate::allows('import presensi')) {
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

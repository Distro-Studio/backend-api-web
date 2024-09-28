<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\DetailGaji;
use App\Models\Notifikasi;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\PenyesuaianGaji;
use App\Helpers\CalculateHelper;
use App\Helpers\DetailGajiHelper;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\Penggajian\CreateGajiJob;
use App\Exports\Keuangan\PenyesuaianGajiExport;
use App\Http\Requests\StorePenyesuaianGajiCustomRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PenyesuaianGajiController extends Controller
{
  // public function index(Request $request)
  // {
  //   if (!Gate::allows('view penggajianKaryawan')) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
  //   }

  //   // Tentukan limit default
  //   $limit = $request->input('limit', 10); // Default 10 jika tidak ada atau kosong

  //   $PenyesuaianGaji = PenyesuaianGaji::query()->orderBy('created_at', 'desc');

  //   // Ambil semua filter dari request body
  //   $filters = $request->all();

  //   // Filter
  //   if (isset($filters['unit_kerja'])) {
  //     $namaUnitKerja = $filters['unit_kerja'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
  //       if (is_array($namaUnitKerja)) {
  //         $query->whereIn('id', $namaUnitKerja);
  //       } else {
  //         $query->where('id', '=', $namaUnitKerja);
  //       }
  //     });
  //   }

  //   if (isset($filters['jabatan'])) {
  //     $namaJabatan = $filters['jabatan'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
  //       if (is_array($namaJabatan)) {
  //         $query->whereIn('id', $namaJabatan);
  //       } else {
  //         $query->where('id', '=', $namaJabatan);
  //       }
  //     });
  //   }

  //   if (isset($filters['status_karyawan'])) {
  //     $statusKaryawan = $filters['status_karyawan'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
  //       if (is_array($statusKaryawan)) {
  //         $query->whereIn('id', $statusKaryawan);
  //       } else {
  //         $query->where('id', '=', $statusKaryawan);
  //       }
  //     });
  //   }

  //   if (isset($filters['masa_kerja'])) {
  //     $masaKerja = $filters['masa_kerja'];
  //     if (is_array($masaKerja)) {
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($masaKerja) {
  //         foreach ($masaKerja as $masa) {
  //           $bulan = $masa * 12;
  //           $query->orWhereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
  //         }
  //       });
  //     } else {
  //       $bulan = $masaKerja * 12;
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($bulan) {
  //         $query->whereRaw('TIMESTAMPDIFF(MONTH, tgl_masuk, COALESCE(tgl_keluar, NOW())) <= ?', [$bulan]);
  //       });
  //     }
  //   }

  //   if (isset($filters['status_aktif'])) {
  //     $statusAktif = $filters['status_aktif'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.users', function ($query) use ($statusAktif) {
  //       if (is_array($statusAktif)) {
  //         $query->whereIn('status_aktif', $statusAktif);
  //       } else {
  //         $query->where('status_aktif', '=', $statusAktif);
  //       }
  //     });
  //   }

  //   if (isset($filters['tgl_masuk'])) {
  //     $tglMasuk = $filters['tgl_masuk'];
  //     if (is_array($tglMasuk)) {
  //       $convertedDates = array_map([RandomHelper::class, 'convertToDateString'], $tglMasuk);
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($convertedDates) {
  //         $query->whereIn('tgl_masuk', $convertedDates);
  //       });
  //     } else {
  //       $convertedDate = RandomHelper::convertToDateString($tglMasuk);
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($convertedDate) {
  //         $query->where('tgl_masuk', $convertedDate);
  //       });
  //     }
  //   }

  //   if (isset($filters['agama'])) {
  //     $namaAgama = $filters['agama'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
  //       if (is_array($namaAgama)) {
  //         $query->whereIn('id', $namaAgama);
  //       } else {
  //         $query->where('id', '=', $namaAgama);
  //       }
  //     });
  //   }

  //   if (isset($filters['jenis_kelamin'])) {
  //     $jenisKelamin = $filters['jenis_kelamin'];
  //     if (is_array($jenisKelamin)) {
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($jenisKelamin) {
  //         $query->where(function ($query) use ($jenisKelamin) {
  //           foreach ($jenisKelamin as $jk) {
  //             $query->orWhere('jenis_kelamin', $jk);
  //           }
  //         });
  //       });
  //     } else {
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans', function ($query) use ($jenisKelamin) {
  //         $query->where('jenis_kelamin', $jenisKelamin);
  //       });
  //     }
  //   }

  //   if (isset($filters['pendidikan_terakhir'])) {
  //     $namaPendidikan = $filters['pendidikan_terakhir'];
  //     $PenyesuaianGaji->whereHas('penggajians.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
  //       if (is_array($namaPendidikan)) {
  //         $query->whereIn('id', $namaPendidikan);
  //       } else {
  //         $query->where('id', '=', $namaPendidikan);
  //       }
  //     });
  //   }

  //   if (isset($filters['jenis_karyawan'])) {
  //     $jenisKaryawan = $filters['jenis_karyawan'];
  //     if (is_array($jenisKaryawan)) {
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
  //         $query->where(function ($query) use ($jenisKaryawan) {
  //           foreach ($jenisKaryawan as $jk) {
  //             $query->orWhere('jenis_karyawan', $jk);
  //           }
  //         });
  //       });
  //     } else {
  //       $PenyesuaianGaji->whereHas('penggajians.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
  //         $query->where('jenis_karyawan', $jenisKaryawan);
  //       });
  //     }
  //   }

  //   // Search
  //   if (isset($filters['search'])) {
  //     $searchTerm = '%' . $filters['search'] . '%';
  //     $PenyesuaianGaji->where(function ($query) use ($searchTerm) {
  //       $query->whereHas('penggajians.data_karyawans.users', function ($query) use ($searchTerm) {
  //         $query->where('nama', 'like', $searchTerm);
  //       })->orWhereHas('penggajians.data_karyawans', function ($query) use ($searchTerm) {
  //         $query->where('nik', 'like', $searchTerm);
  //       });
  //     });
  //   }

  //   if ($limit == 0) {
  //     $dataPenyesuaianGaji = $PenyesuaianGaji->get();
  //     $paginationData = null;
  //   } else {
  //     $limit = is_numeric($limit) ? (int) $limit : 10;
  //     $dataPenyesuaianGaji = $PenyesuaianGaji->paginate($limit);

  //     $paginationData = [
  //       'links' => [
  //         'first' => $dataPenyesuaianGaji->url(1),
  //         'last' => $dataPenyesuaianGaji->url($dataPenyesuaianGaji->lastPage()),
  //         'prev' => $dataPenyesuaianGaji->previousPageUrl(),
  //         'next' => $dataPenyesuaianGaji->nextPageUrl(),
  //       ],
  //       'meta' => [
  //         'current_page' => $dataPenyesuaianGaji->currentPage(),
  //         'last_page' => $dataPenyesuaianGaji->lastPage(),
  //         'per_page' => $dataPenyesuaianGaji->perPage(),
  //         'total' => $dataPenyesuaianGaji->total(),
  //       ]
  //     ];
  //   }
  //   if ($dataPenyesuaianGaji->isEmpty()) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Tidak ada data penyesuaian pengggajian karyawan yang tersedia.'), Response::HTTP_OK);
  //   }

  //   $formattedData = $dataPenyesuaianGaji->map(function ($penyesuaianGaji) {
  //     return [
  //       'id' => $penyesuaianGaji->id,
  //       'user' => $penyesuaianGaji->penggajians->data_karyawans->users,
  //       'unit_kerja' => $penyesuaianGaji->penggajians->data_karyawans->unit_kerjas,
  //       'kelompok_gaji' => $penyesuaianGaji->penggajians->data_karyawans->kelompok_gajis,
  //       'ptkp' => $penyesuaianGaji->penggajians->data_karyawans->ptkps,
  //       'kategori_gaji_id' => $penyesuaianGaji->kategori_gajis,
  //       'nama_detail' => $penyesuaianGaji->nama_detail,
  //       'besaran' => $penyesuaianGaji->besaran,
  //       'bulan_mulai' => $penyesuaianGaji->bulan_mulai,
  //       'bulan_selesai' => $penyesuaianGaji->bulan_selesai,
  //       'created_at' => $penyesuaianGaji->created_at,
  //       'updated_at' => $penyesuaianGaji->updated_at
  //     ];
  //   });

  //   return response()->json([
  //     'status' => Response::HTTP_OK,
  //     'message' => 'Data penyesuaian pengggajian karyawan berhasil ditampilkan.',
  //     'data' => $formattedData,
  //     'pagination' => $paginationData
  //   ], Response::HTTP_OK);
  // }

  public function exportPenyesuaianGaji()
  {
    if (!Gate::allows('export penggajianKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $dataPenyesuaian = PenyesuaianGaji::all();
    if ($dataPenyesuaian->isEmpty()) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data penyesuaian gaji karyawan yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
    }

    try {
      return Excel::download(new PenyesuaianGajiExport(), 'penyesuaian-gaji-karyawan.xls');
    } catch (\Throwable $e) {
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  // Penyesuaian gaji multi
  // public function storePenyesuaianGajiPenambah(StorePenyesuaianGajiCustomRequest $request)
  // {
  //   if (!Gate::allows('create penggajianKaryawan')) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
  //   }

  //   $data = $request->validated();
  //   $userIds = $request->input('user_id', []);

  //   if (empty($userIds)) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Silahkan pilih karyawan terlebih dahulu untuk melanjutkan proses penyesuaian penambahan gaji.'), Response::HTTP_BAD_REQUEST);
  //   }

  //   DB::beginTransaction();
  //   try {
  //     // Fetch data_karyawan_id from users table based on user_ids
  //     $dataKaryawanIds = DB::table('users')
  //       ->whereIn('id', $userIds)
  //       ->pluck('data_karyawan_id')
  //       ->toArray();

  //     if (empty($dataKaryawanIds)) {
  //       return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Proses tidak dapat dilanjutkan, Karena sistem tidak dapat menemukan data karyawan yang terkait.'), Response::HTTP_NOT_FOUND);
  //     }

  //     $currentMonth = Carbon::now('Asia/Jakarta')->month;
  //     $currentYear = Carbon::now('Asia/Jakarta')->year;
  //     $penggajianIds = DB::table('penggajians')
  //       ->whereMonth('tgl_penggajian', $currentMonth)
  //       ->whereYear('tgl_penggajian', $currentYear)
  //       ->whereIn('data_karyawan_id', $dataKaryawanIds)
  //       ->pluck('id', 'data_karyawan_id')
  //       ->toArray();

  //     if (empty($penggajianIds)) {
  //       return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Proses tidak dapat dilanjutkan, Karena sistem tidak dapat menemukan data penggajian karyawan yang terkait.'), Response::HTTP_NOT_FOUND);
  //     }

  //     // Loop through each penggajian_id and create PenyesuaianGaji
  //     foreach ($penggajianIds as $data_karyawan_id => $penggajian_id) {
  //       $penyesuaianGaji = PenyesuaianGaji::create([
  //         'penggajian_id' => $penggajian_id,
  //         'kategori_gaji_id' => 2, // Penambah
  //         'nama_detail' => $data['nama_detail'],
  //         'besaran' => $data['besaran'],
  //         'bulan_mulai' => $data['bulan_mulai'],
  //         'bulan_selesai' => $data['bulan_selesai'],
  //       ]);

  //       $penggajian = Penggajian::find($penggajian_id);

  //       // Check if the month and year match the current month
  //       $bulanMulai = Carbon::parse(RandomHelper::convertSpecialDateFormat($data['bulan_mulai']));

  //       if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
  //         $penggajian->gaji_bruto += $request->besaran;

  //         $totalPremi = CalculateHelper::calculatedPremi($penggajian->data_karyawans->id, $penggajian->gaji_bruto, $penggajian->data_karyawans->kelompok_gajis->gaji_pokok);

  //         if ($currentMonth >= 1 && $currentMonth <= 11) {
  //           $pph21 = CalculateHelper::calculatedPPH21ForMonths($penggajian->gaji_bruto, $penggajian->data_karyawans->ptkp_id);
  //         } else {
  //           $bonusBOR = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Bonus BOR')
  //             ->sum('besaran') ?: 0;
  //           $bonusPresensi = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Bonus Presensi')
  //             ->sum('besaran') ?: 0;
  //           $bonusUangLembur = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Uang Lembur')
  //             ->sum('besaran') ?: 0;

  //           $totalReward = $bonusBOR + $bonusPresensi + $bonusUangLembur;
  //           $dataKaryawan = $penggajian->data_karyawans;
  //           $pph21 = CalculateHelper::calculatedPPH21ForDecember($dataKaryawan, $totalReward);
  //         }
  //         $takeHomePay = $penggajian->gaji_bruto - $totalPremi - $pph21;

  //         $penggajian->pph_21 = $pph21;
  //         $penggajian->take_home_pay = $takeHomePay;
  //         $penggajian->save();

  //         // Update PPh21 in DetailGaji
  //         DetailGaji::where('penggajian_id', $penggajian_id)->where('nama_detail', 'PPh21')->update(['besaran' => $pph21]);

  //         // Save detail gaji for this adjustment
  //         DetailGaji::create([
  //           'penggajian_id' => $penggajian_id,
  //           'kategori_gaji_id' => 2,
  //           'nama_detail' => $penyesuaianGaji->nama_detail,
  //           'besaran' => $penyesuaianGaji->besaran
  //         ]);
  //       }
  //     }

  //     $this->createNotifikasiPenyesuaianGaji($userIds, $penyesuaianGaji);

  //     DB::commit();

  //     return response()->json([
  //       'status' => Response::HTTP_OK,
  //       'message' => "Penambahan penggajian '{$data['nama_detail']}' berhasil dilakukan untuk semua karyawan terkait."
  //     ], Response::HTTP_OK);
  //   } catch (\Exception $e) {
  //     DB::rollBack();
  //     return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
  //   }
  // }

  // public function storePenyesuaianGajiPengurang(StorePenyesuaianGajiCustomRequest $request)
  // {
  //   if (!Gate::allows('create penggajianKaryawan')) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
  //   }

  //   $data = $request->validated();
  //   $userIds = $request->input('user_id', []);

  //   if (empty($userIds)) {
  //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Silahkan pilih karyawan terlebih dahulu untuk melanjutkan proses penyesuaian pengurangan gaji.'), Response::HTTP_BAD_REQUEST);
  //   }

  //   DB::beginTransaction();
  //   try {
  //     // Fetch data_karyawan_id dari tabel users berdasarkan user_ids
  //     $dataKaryawanIds = DB::table('users')
  //       ->whereIn('id', $userIds)
  //       ->pluck('data_karyawan_id')
  //       ->toArray();

  //     if (empty($dataKaryawanIds)) {
  //       return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Proses tidak dapat dilanjutkan, Karena sistem tidak dapat menemukan data karyawan yang terkait.'), Response::HTTP_NOT_FOUND);
  //     }

  //     $currentMonth = Carbon::now('Asia/Jakarta')->month;
  //     $currentYear = Carbon::now('Asia/Jakarta')->year;
  //     $penggajianIds = DB::table('penggajians')
  //       ->whereMonth('tgl_penggajian', $currentMonth)
  //       ->whereYear('tgl_penggajian', $currentYear)
  //       ->whereIn('data_karyawan_id', $dataKaryawanIds)
  //       ->pluck('id', 'data_karyawan_id')
  //       ->toArray();

  //     if (empty($penggajianIds)) {
  //       return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Proses tidak dapat dilanjutkan, Karena sistem tidak dapat menemukan data penggajian karyawan yang terkait.'), Response::HTTP_NOT_FOUND);
  //     }

  //     // Loop untuk setiap penggajian_id dan buat PenyesuaianGaji
  //     foreach ($penggajianIds as $data_karyawan_id => $penggajian_id) {
  //       $penyesuaianGaji = PenyesuaianGaji::create([
  //         'penggajian_id' => $penggajian_id,
  //         'kategori_gaji_id' => 3, // Pengurang
  //         'nama_detail' => $data['nama_detail'],
  //         'besaran' => $data['besaran'],
  //         'bulan_mulai' => $data['bulan_mulai'],
  //         'bulan_selesai' => $data['bulan_selesai'],
  //       ]);

  //       $penggajian = Penggajian::find($penggajian_id);

  //       // Periksa apakah bulan dan tahun mulai sesuai dengan bulan dan tahun saat ini
  //       $bulanMulai = Carbon::parse(RandomHelper::convertSpecialDateFormat($data['bulan_mulai']));

  //       if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
  //         $penggajian->gaji_bruto -= $request->besaran;

  //         $totalPremi = CalculateHelper::calculatedPremi($penggajian->data_karyawans->id, $penggajian->gaji_bruto, $penggajian->data_karyawans->kelompok_gajis->gaji_pokok);

  //         if ($currentMonth >= 1 && $currentMonth <= 11) {
  //           $penggajian->take_home_pay -= $data['besaran'];
  //           $penggajian->save();

  //           DetailGaji::create([
  //             'penggajian_id' => $penggajian_id,
  //             'kategori_gaji_id' => 3,
  //             'nama_detail' => $penyesuaianGaji->nama_detail,
  //             'besaran' => $penyesuaianGaji->besaran
  //           ]);
  //         } else {
  //           $bonusBOR = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Bonus BOR')
  //             ->sum('besaran') ?: 0;
  //           $bonusPresensi = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Bonus Presensi')
  //             ->sum('besaran') ?: 0;
  //           $bonusUangLembur = DetailGaji::where('penggajian_id', $penggajian->id)
  //             ->where('nama_detail', 'Uang Lembur')
  //             ->sum('besaran') ?: 0;

  //           $totalReward = $bonusBOR + $bonusPresensi + $bonusUangLembur;
  //           $dataKaryawan = $penggajian->data_karyawans;
  //           $pph21 = CalculateHelper::calculatedPPH21ForDecember($dataKaryawan, $totalReward);
  //           $takeHomePay = $penggajian->gaji_bruto - $totalPremi - $pph21;

  //           $penggajian->pph_21 = $pph21;
  //           $penggajian->take_home_pay = $takeHomePay;
  //           $penggajian->save();

  //           DetailGaji::where('penggajian_id', $penggajian->id)->where('nama_detail', 'PPh21')->update(['besaran' => $pph21]);

  //           DetailGaji::create([
  //             'penggajian_id' => $penggajian_id,
  //             'kategori_gaji_id' => 3,
  //             'nama_detail' => $penyesuaianGaji->nama_detail,
  //             'besaran' => $penyesuaianGaji->besaran
  //           ]);
  //         }
  //       }
  //     }

  //     // Kirim notifikasi tanpa query lagi
  //     $this->createNotifikasiPenyesuaianGaji($userIds, $penyesuaianGaji);

  //     DB::commit();

  //     return response()->json([
  //       'status' => Response::HTTP_OK,
  //       'message' => "Pengurangan gaji '{$data['nama_detail']}' berhasil dilakukan untuk karyawan terkait."
  //     ], Response::HTTP_OK);
  //   } catch (\Exception $e) {
  //     DB::rollBack();
  //     return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
  //   }
  // }

  // private function createNotifikasiPenyesuaianGaji($userIds, $penyesuaianGaji)
  // {
  //   foreach ($userIds as $user_id) {
  //     if ($penyesuaianGaji->kategori_gaji_id == 2) {
  //       $message = "Anda telah mendapatkan penambahan gaji '{$penyesuaianGaji->nama_detail}', Silahkan lakukan pengecekkan kembali dan pastikan gaji anda telah sesuai.";
  //     } else {
  //       $message = "Anda telah mendapatkan pengurangan gaji '{$penyesuaianGaji->nama_detail}', Silahkan lakukan pengecekkan kembali dan pastikan gaji anda telah sesuai.";
  //     }

  //     // Buat notifikasi untuk user yang terkait
  //     Notifikasi::create([
  //       'kategori_notifikasi_id' => 9,
  //       'user_id' => $user_id,
  //       'message' => $message,
  //       'is_read' => false,
  //       'created_at' => Carbon::now('Asia/Jakarta'),
  //     ]);
  //   }
  // }

  public function penyesuaianBOR(Request $request)
  {
    if (!Gate::allows('create penggajianKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $jadwalPenggajian = DB::table('jadwal_penggajians')
      ->select('tgl_mulai')
      ->orderBy('tgl_mulai', 'desc')
      ->first();
    if (!$jadwalPenggajian) {
      return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Tidak ada tanggal penggajian yang tersedia.'), Response::HTTP_BAD_REQUEST);
    }

    $currentMonth = Carbon::now('Asia/Jakarta')->month;
    $currentYear = Carbon::now('Asia/Jakarta')->year;
    $tgl_mulai = Carbon::createFromFormat('Y-m-d', "$currentYear-$currentMonth-{$jadwalPenggajian->tgl_mulai}", 'Asia/Jakarta');
    $tgl_akhir = $tgl_mulai->copy()->endOfDay();
    $awalBulan = Carbon::now('Asia/Jakarta')->startOfMonth();
    $currentDateTime = Carbon::now('Asia/Jakarta');

    if ($currentDateTime->lessThan($awalBulan) || $currentDateTime->greaterThan($tgl_akhir)) {
      return response()->json(new WithoutDataResource(
        Response::HTTP_BAD_REQUEST,
        "Perhitungan ulang BOR hanya dapat dilakukan mulai tanggal 1 hingga tanggal '{$tgl_mulai->format('d-m-Y')}' sampai jam 23:59."
      ), Response::HTTP_BAD_REQUEST);
    }

    // 1. Ambil riwayat penggajian dari request dan validasi untuk periode bulan dan tahun saat ini
    $riwayatPenggajianId = $request->input('riwayat_penggajian_id');
    if (!$riwayatPenggajianId) {
      return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'ID riwayat penggajian tidak ditemukan.'), Response::HTTP_BAD_REQUEST);
    }

    // Validasi riwayat penggajian untuk bulan dan tahun saat ini
    $riwayatPenggajian = RiwayatPenggajian::where('id', $riwayatPenggajianId)
      ->whereMonth('periode', $currentMonth)
      ->whereYear('periode', $currentYear)
      ->first();

    if (!$riwayatPenggajian) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat penggajian tidak ditemukan untuk periode bulan dan tahun saat ini.'), Response::HTTP_NOT_FOUND);
    }

    // Tambahkan validasi untuk status_gaji_id == 2 (sudah dipublikasi)
    if ($riwayatPenggajian->status_gaji_id == 2) {
      return response()->json(new WithoutDataResource(
        Response::HTTP_BAD_REQUEST,
        'Penyesuaian ulang penggajian tidak diperbolehkan karena penggajian sudah dipublikasi.'
      ), Response::HTTP_BAD_REQUEST);
    }

    $data_karyawan_ids = DataKaryawan::where('id', '!=', 1)
      ->whereHas('users', function ($query) {
        $query->where('status_aktif', 2);
      })
      ->where('status_karyawan_id', [1, 2, 3])
      ->pluck('id')
      ->toArray();
    $sertakan_bor = $request->has('bor') && $request->bor == 1;

    DB::beginTransaction();
    try {
      $penggajians = Penggajian::where('riwayat_penggajian_id', $riwayatPenggajianId)->pluck('id');

      DetailGaji::whereIn('penggajian_id', $penggajians)->delete();
      PenyesuaianGaji::whereIn('penggajian_id', $penggajians)->delete();
      Penggajian::where('riwayat_penggajian_id', $riwayatPenggajianId)->delete();

      CreateGajiJob::dispatch($data_karyawan_ids, $sertakan_bor, $riwayatPenggajian->id);

      DB::commit();

      $periodeAt = Carbon::parse($riwayatPenggajian->periode)->locale('id')->isoFormat('MMMM Y');
      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Penyesuaian ulang penggajian berhasil dilakukan untuk semua karyawan pada periode '{$periodeAt}'. Segala bentuk penyesuaian gaji penambah atau pengurang telah di-reset."
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat melakukan Penyesuaian ulang penggajian: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  // Penyesuaian gaji single
  public function storePenyesuaianGajiPenambah(StorePenyesuaianGajiCustomRequest $request, $penggajian_id)
  {
    if (!Gate::allows('create penggajianKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $verifikatorId = Auth::id();

    $data = $request->validated();

    // Cek apakah penggajian_id valid
    $penggajian = Penggajian::find($penggajian_id);
    if (!$penggajian) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $gaji_pokok = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Gaji Pokok');
    $tunjangan_jabatan = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Jabatan');
    $tunjangan_fungsional = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Fungsional');
    $tunjangan_khusus = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Khusus');
    $tunjangan_lainnya = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Lainnya');
    $bonusBOR = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Bonus BOR');
    $bonusPresensi = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Bonus Presensi');
    $bonusUangLembur = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Uang Lembur');
    $thr = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'THR');
    $uang_makan = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Uang Makan');
    $koperasi = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Koperasi');
    $obat = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Obat/Perawatan');
    $totalTagihanPotongan = $koperasi + $obat;

    $totalReward = $bonusBOR + $bonusPresensi + $bonusUangLembur;

    DB::beginTransaction();
    try {
      // Simpan penyesuaian gaji
      $penyesuaianGaji = PenyesuaianGaji::create([
        'penggajian_id' => $penggajian_id,
        'kategori_gaji_id' => 2,
        'nama_detail' => $request->nama_detail,
        'besaran' => $request->besaran,
        'bulan_mulai' => $request->bulan_mulai,
        'bulan_selesai' => $request->bulan_selesai,
      ]);

      // Cek apakah bulan mulai adalah bulan saat ini
      $currentMonth = Carbon::now('Asia/Jakarta')->month;
      $currentYear = Carbon::now('Asia/Jakarta')->year;
      $bulanMulai = Carbon::parse(RandomHelper::convertSpecialDateFormat($request->bulan_mulai));

      if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
        $penggajian->gaji_bruto += $request->besaran;

        $brutoPremi = $gaji_pokok +
          $tunjangan_jabatan +
          $tunjangan_fungsional +
          $tunjangan_khusus +
          $tunjangan_lainnya;

        $totalPremi = CalculateHelper::calculatedPremi($penggajian->data_karyawans->id, $brutoPremi, $penggajian->gaji_bruto, $gaji_pokok);

        if ($currentMonth >= 1 && $currentMonth <= 11) {
          $pph21 = CalculateHelper::calculatedPPH21ForMonths($penggajian->gaji_bruto, $penggajian->data_karyawans->ptkp_id);
        } else {
          $pph21 = CalculateHelper::calculatedPPH21ForDecember($penggajian->data_karyawans->id, $penggajian->gaji_bruto, $totalPremi,  $totalTagihanPotongan, $penggajian->data_karyawans->ptkp_id);
        }

        $takeHomePay = $penggajian->gaji_bruto - $totalPremi - $pph21;
        $penggajian->pph_21 = $pph21;
        $penggajian->take_home_pay = $takeHomePay;
        $penggajian->save();

        DetailGaji::where('penggajian_id', $penggajian->id)->where('nama_detail', 'PPH21')->update(['besaran' => $pph21]);

        // Simpan detail gaji ke tabel detail_gajis
        DetailGaji::create([
          'penggajian_id' => $penggajian_id,
          'kategori_gaji_id' => 2,
          'nama_detail' => $penyesuaianGaji->nama_detail,
          'besaran' => $penyesuaianGaji->besaran
        ]);

        $this->createNotifikasiPenyesuaianGaji($verifikatorId, $penggajian, $penyesuaianGaji);
      }
      DB::commit();

      $userName = $penggajian->data_karyawans->users->nama;

      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Penambahan penggajian '{$penyesuaianGaji->nama_detail}' berhasil dilakukan untuk karyawan '{$userName}'."
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function storePenyesuaianGajiPengurang(StorePenyesuaianGajiCustomRequest $request, $penggajian_id)
  {
    if (!Gate::allows('create penggajianKaryawan')) {
      return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    }

    $verifikatorId = Auth::id();
    $data = $request->validated();

    // Cek apakah penggajian_id valid
    $penggajian = Penggajian::find($penggajian_id);
    if (!$penggajian) {
      return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data penggajian tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    }

    $gaji_pokok = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Gaji Pokok');
    $tunjangan_jabatan = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Jabatan');
    $tunjangan_fungsional = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Fungsional');
    $tunjangan_khusus = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Khusus');
    $tunjangan_lainnya = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Tunjangan Lainnya');
    $bonusBOR = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Bonus BOR');
    $bonusPresensi = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Bonus Presensi');
    $bonusUangLembur = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Uang Lembur');
    $koperasi = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Koperasi');
    $obat = DetailGajiHelper::getDetailGajiByNamaDetail($penggajian->id, 'Obat/Perawatan');
    $totalTagihanPotongan = $koperasi + $obat;

    $totalReward = $bonusBOR + $bonusPresensi + $bonusUangLembur;

    DB::beginTransaction();
    try {
      // Simpan penyesuaian gaji
      $penyesuaianGaji = PenyesuaianGaji::create([
        'penggajian_id' => $penggajian_id,
        'kategori_gaji_id' => 3,
        'nama_detail' => $request->nama_detail,
        'besaran' => $request->besaran,
        'bulan_mulai' => $request->bulan_mulai,
        'bulan_selesai' => $request->bulan_selesai,
      ]);

      // Cek apakah bulan mulai adalah bulan saat ini
      $currentMonth = Carbon::now('Asia/Jakarta')->month;
      $currentYear = Carbon::now('Asia/Jakarta')->year;
      $bulanMulai = Carbon::parse(RandomHelper::convertSpecialDateFormat($request->bulan_mulai));

      if ($bulanMulai->month == $currentMonth && $bulanMulai->year == $currentYear) {
        $penggajian->gaji_bruto -= $request->besaran;

        $brutoPremi = $gaji_pokok +
          $tunjangan_jabatan +
          $tunjangan_fungsional +
          $tunjangan_khusus +
          $tunjangan_lainnya;

        $totalPremi = CalculateHelper::calculatedPremi($penggajian->data_karyawans->id, $brutoPremi, $penggajian->gaji_bruto, $gaji_pokok);

        if ($currentMonth >= 1 && $currentMonth <= 11) {
          // Kurangi take home pay dengan besaran penyesuaian yang baru dibuat
          $penggajian->take_home_pay -= $data['besaran'];
          $penggajian->save();
          DetailGaji::create([
            'penggajian_id' => $penggajian_id,
            'kategori_gaji_id' => 3,
            'nama_detail' => $penyesuaianGaji->nama_detail,
            'besaran' => $penyesuaianGaji->besaran
          ]);
        } else {
          $dataKaryawan = $penggajian->data_karyawans->id;
          $pph21 = CalculateHelper::calculatedPPH21ForDecember($dataKaryawan, $penggajian->gaji_bruto, $totalPremi,  $totalTagihanPotongan, $penggajian->data_karyawans->ptkp_id);

          $takeHomePay = $penggajian->gaji_bruto - $totalPremi - $pph21;

          $penggajian->pph_21 = $pph21;
          $penggajian->take_home_pay = $takeHomePay;
          $penggajian->save();

          DetailGaji::where('penggajian_id', $penggajian->id)->where('nama_detail', 'PPH21')->update(['besaran' => $pph21]);

          // Simpan detail gaji ke tabel detail_gajis
          DetailGaji::create([
            'penggajian_id' => $penggajian_id,
            'kategori_gaji_id' => 3,
            'nama_detail' => $penyesuaianGaji->nama_detail,
            'besaran' => $penyesuaianGaji->besaran
          ]);
        }
      }

      $this->createNotifikasiPenyesuaianGaji($verifikatorId, $penggajian, $penyesuaianGaji);

      DB::commit();

      $userName = $penggajian->data_karyawans->users->nama;
      return response()->json([
        'status' => Response::HTTP_OK,
        'message' => "Pengurangan penggajian '{$penyesuaianGaji->nama_detail}' berhasil dilakukan untuk karyawan '{$userName}'."
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan penyesuaian gaji: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  private function createNotifikasiPenyesuaianGaji($verifikatorId, $penggajian, $penyesuaianGaji)
  {
    $user = $penggajian->data_karyawans->users;

    if ($penyesuaianGaji->kategori_gaji_id == 2) {
      $message = "Anda telah mendapatkan penambahan gaji '{$penyesuaianGaji->nama_detail}', Silahkan lakukan pengecekkan kembali dan pastikan gaji anda telah sesuai.";
    } else {
      $message = "Anda telah mendapatkan pengurangan gaji '{$penyesuaianGaji->nama_detail}', Silahkan lakukan pengecekkan kembali dan pastikan gaji anda telah sesuai.";
    }

    $userIds = [$user->id, $verifikatorId];
    if (!in_array(1, $userIds)) {
      $userIds[] = 1;
    }
    $userIdsJson = json_encode($userIds);

    // Buat notifikasi untuk user yang terkait
    Notifikasi::create([
      'kategori_notifikasi_id' => 9,
      'user_id' => $userIdsJson,
      'message' => $message,
      'is_read' => false,
      'created_at' => Carbon::now('Asia/Jakarta'),
    ]);
  }
}

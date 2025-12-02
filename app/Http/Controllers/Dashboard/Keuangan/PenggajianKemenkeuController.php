<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use ZipArchive;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Spatie\Browsershot\Browsershot;

use function Psy\debug;

class PenggajianKemenkeuController extends Controller
{
    // ** Export Rekap Potongan Pak Bondan **
    public function rekapPDFKemenkeu(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        ini_set('memory_limit', '512M');
        set_time_limit(400);

        $riwayatPenggajianId = $request->input('riwayat_penggajian_id');
        $years = $request->input('years', []);
        if (empty($years) || !is_array($years)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Harap pilih setidaknya satu tahun untuk diekspor.'), Response::HTTP_BAD_REQUEST);
        }

        // $currentMonth = Carbon::now('Asia/Jakarta')->month;
        // if ($currentMonth < 12) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Ekspor hanya diperbolehkan jika sudah memasuki bulan Desember.'), Response::HTTP_BAD_REQUEST);
        // }

        $riwayatGajiCheck = RiwayatPenggajian::find($riwayatPenggajianId)->pluck('id')->first();
        if (!$riwayatGajiCheck) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Riwayat penggajian yang dipilih tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $statusGajiCheck = Penggajian::where('riwayat_penggajian_id', $riwayatGajiCheck)
            ->whereIn(DB::raw('YEAR(tgl_penggajian)'), $years)
            ->pluck('status_gaji_id')
            ->contains(1);
        if ($statusGajiCheck) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Penggajian periode saat ini belum dilakukan publikasi, silahkan coba lagi setelah dilakukan publikasi.'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $zipFileName = 'penggajian_karyawan_kemenkeu.zip';
            $zipFilePath = storage_path('app/public/' . $zipFileName);
            $zip = new ZipArchive();

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal membuat file ZIP.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Ambil data penggajian berdasarkan riwayat_penggajian_id dalam batch kecil
            Penggajian::where('riwayat_penggajian_id', $riwayatPenggajianId)
                ->whereIn(DB::raw('YEAR(tgl_penggajian)'), $years)
                ->where('status_gaji_id', 2)
                ->chunk(100, function ($penggajians) use ($zip, $years) {
                    foreach ($penggajians as $penggajian) {
                        $karyawanId = $penggajian->data_karyawan_id;

                        // Ambil data karyawan
                        $dataKaryawan = DataKaryawan::with(['jabatans', 'users', 'ptkps'])->find($karyawanId);
                        if (!$dataKaryawan) {
                            continue;
                        }

                        // Get detail_gajis for the Penggajian
                        $gajiPokok = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Gaji Pokok')->sum('besaran');
                        });

                        $tunjanganJabatan = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Jabatan')->sum('besaran');
                        });

                        $tunjanganFungsional = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Fungsional')->sum('besaran');
                        });

                        $tunjanganKhusus = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Khusus')->sum('besaran');
                        });

                        $tunjanganLainnya = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Lainnya')->sum('besaran');
                        });

                        $uangLembur = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Uang Lembur')->sum('besaran');
                        });

                        $rewardBOR = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Reward BOR')->sum('besaran');
                        });

                        $rewardAbsensi = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Reward Absensi')->sum('besaran');
                        });

                        $uangMakan = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('nama_detail', 'Uang Makan')->sum('besaran');
                        });

                        $tambahanLain = $penggajians->sum(function ($penggajian) {
                            return $penggajian->detail_gajis->where('kategori_gaji_id', 2)
                                ->whereNotIn('nama_detail', [
                                    'Gaji Pokok',
                                    'Tunjangan Jabatan',
                                    'Tunjangan Fungsional',
                                    'Tunjangan Khusus',
                                    'Tunjangan Lainnya',
                                    'Uang Lembur',
                                    'Uang Makan',
                                    'Reward BOR',
                                    'Reward Absensi',
                                ])->sum('besaran');
                        });

                        $pdf = Pdf::loadView('gajis.report_kemenkeu', [
                            'gajiPokok' => $gajiPokok,
                            'tunjanganJabatan' => $tunjanganJabatan,
                            'tunjanganFungsional' => $tunjanganFungsional,
                            'tunjanganKhusus' => $tunjanganKhusus,
                            'tunjanganLainnya' => $tunjanganLainnya,
                            'uangLembur' => $uangLembur,
                            'rewardBOR' => $rewardBOR,
                            'rewardAbsensi' => $rewardAbsensi,
                            'uangMakan' => $uangMakan,
                            'karyawanId' => $karyawanId,
                            'namaUser' => $dataKaryawan->users->nama ?? '-',
                            'npwpUser' => $dataKaryawan->npwp ?? '-',
                            'nikUser' => $dataKaryawan->nik ?? '-',
                            'alamatUser' => $dataKaryawan->alamat ?? '-',
                            'jenisKelaminUser' => $dataKaryawan->jenis_kelamin ? 'LAKI-LAKI' : 'PEREMPUAN',
                            'jabatanUser' => $dataKaryawan->jabatans->nama_jabatan ?? '-',
                            'jenisTanggunganUser' => $dataKaryawan->ptkps->kode_ptkp ?? '-',
                            'years' => $years,
                        ])->setPaper('A4', 'portrait');

                        $fileName = 'penggajian_karyawan_' . $karyawanId . '_tahun_' . implode('_', $years) . '.pdf';
                        $pdfFilePath = storage_path('app/public/' . $fileName);

                        // Simpan PDF ke dalam storage
                        Storage::put('public/' . $fileName, $pdf->output());

                        // Tambahkan PDF ke dalam ZIP
                        $zip->addFile($pdfFilePath, $fileName);
                    }
                });

            $zip->close();

            // return response()->download($zipFilePath)->deleteFileAfterSend(true);
            return response()->download($zipFilePath);
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti atau hubungi SIM RS.'), Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function exportSingleKaryawanPDF(Request $request)
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(
                Response::HTTP_FORBIDDEN,
                'Anda tidak memiliki hak akses untuk melakukan proses ini.'
            ), Response::HTTP_FORBIDDEN);
        }

        ini_set('memory_limit', '512M');
        set_time_limit(1000);

        $karyawanId = $request->input('karyawan_id');
        $riwayatPenggajianId = $request->input('riwayat_penggajian_id');

        // Validasi input
        if (!$karyawanId || !$riwayatPenggajianId) {
            return response()->json(new WithoutDataResource(
                Response::HTTP_BAD_REQUEST,
                'ID karyawan dan riwayat penggajian wajib diisi.'
            ), Response::HTTP_BAD_REQUEST);
        }

        try {
            // Ambil data penggajian untuk karyawan yang dipilih
            $penggajians = Penggajian::where('data_karyawan_id', $karyawanId)
                ->where('riwayat_penggajian_id', $riwayatPenggajianId)
                ->where('status_gaji_id', 2) // Validasi gaji sudah dipublikasikan
                ->get();

            if ($penggajians->isEmpty()) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_NOT_FOUND,
                    'Tidak ada data penggajian yang ditemukan untuk karyawan ini atau gaji belum dipublikasikan.'
                ), Response::HTTP_NOT_FOUND);
            }

            // Ambil data karyawan
            $dataKaryawan = DataKaryawan::with(['jabatans', 'users', 'ptkps'])->find($karyawanId);
            if (!$dataKaryawan) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_NOT_FOUND,
                    'Data karyawan tidak ditemukan.'
                ), Response::HTTP_NOT_FOUND);
            }

            // Hitung detail gaji
            $gajiPokok = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Gaji Pokok')->sum('besaran'));
            $tunjanganJabatan = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Jabatan')->sum('besaran'));
            $tunjanganFungsional = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Fungsional')->sum('besaran'));
            $tunjanganKhusus = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Khusus')->sum('besaran'));
            $tunjanganLainnya = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Tunjangan Lainnya')->sum('besaran'));
            $uangLembur = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Uang Lembur')->sum('besaran'));
            $rewardBOR = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Reward BOR')->sum('besaran'));
            $rewardAbsensi = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Reward Absensi')->sum('besaran'));
            $uangMakan = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('nama_detail', 'Uang Makan')->sum('besaran'));
            $tambahanLain = $penggajians->sum(fn($penggajian) => $penggajian->detail_gajis->where('kategori_gaji_id', 2)
                ->whereNotIn('nama_detail', [
                    'Gaji Pokok',
                    'Tunjangan Jabatan',
                    'Tunjangan Fungsional',
                    'Tunjangan Khusus',
                    'Tunjangan Lainnya',
                    'Uang Lembur',
                    'Uang Makan',
                    'Reward BOR',
                    'Reward Absensi',
                ])->sum('besaran'));

            $biayaTHR = $penggajians->sum(
                fn($penggajian) => $penggajian->detail_gajis
                    ->where('nama_detail', 'THR')
                    ->where('kategori_gaji_id', 2)
                    ->sum('besaran')
            );
            $biayaPresensi = $penggajians->sum(
                fn($penggajian) => $penggajian->detail_gajis
                    ->where('nama_detail', 'Reward Absensi')
                    ->where('kategori_gaji_id', 2)
                    ->sum('besaran')
            );

            $jmlTHRPresensi = $biayaTHR + $biayaPresensi;

            // Generate PDF menggunakan Snappy
            // Render view menjadi HTML string
            $html = view('gajis.report_kemenkeu', [
                'gajiPokok' => $gajiPokok,
                'tunjanganJabatan' => $tunjanganJabatan,
                'tunjanganFungsional' => $tunjanganFungsional,
                'tunjanganKhusus' => $tunjanganKhusus,
                'tunjanganLainnya' => $tunjanganLainnya,
                'uangLembur' => $uangLembur,
                'rewardBOR' => $rewardBOR,
                'rewardAbsensi' => $rewardAbsensi,
                'uangMakan' => $uangMakan,
                'tambahanLain' => $tambahanLain,
                'tambahanTHRPresensi' => $jmlTHRPresensi,
                'karyawanId' => $karyawanId,
                'namaUser' => $dataKaryawan->users->nama ?? '-',
                'npwpUser' => $dataKaryawan->npwp ?? '-',
                'nikUser' => $dataKaryawan->nik ?? '-',
                'alamatUser' => $dataKaryawan->alamat ?? '-',
                'jenisKelaminUser' => $dataKaryawan->jenis_kelamin ? 'LAKI-LAKI' : 'PEREMPUAN',
                'jabatanUser' => $dataKaryawan->jabatans->nama_jabatan ?? '-',
                'jenisTanggunganUser' => $dataKaryawan->ptkps->kode_ptkp ?? '-',
            ])->render();

            // Generate PDF menggunakan Browsershot
            $pdfPath = storage_path('app/public/penggajian_karyawan_' . $karyawanId . '.pdf');
            Browsershot::html($html)
                ->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox'])
                ->setOption('scale', 1)
                ->setOption('width', 832)
                ->setOption('height', 1280)
                ->margins(2, 2, 2, 2)
                ->showBackground(false)
                ->timeout(200)
                ->savePdf($pdfPath);
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Terjadi kesalahan: ' . $e->getMessage()
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

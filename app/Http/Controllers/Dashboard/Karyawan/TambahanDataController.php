<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Helpers\CalculateBMIHelper;
use App\Http\Controllers\Controller;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\KategoriPendidikan;
use App\Models\Shift;
use App\Models\UnitKerja;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TambahanDataController extends Controller
{
    public function cekNIK(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $niksFromExcel = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('A', 'A'); // Fokus hanya kolom A
                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $nikValue = trim($cell->getValue());
                    if (!empty($nikValue)) {
                        $niksFromExcel[] = $nikValue;
                    }
                }
            }

            // Hilangkan NIK yang duplikat
            $niksFromExcel = array_unique($niksFromExcel);

            // Ambil NIK dari database
            $niksFromDB = DataKaryawan::whereNotNull('nik')->pluck('nik')->toArray();

            // Bandingkan
            $notFoundInExcel = array_diff($niksFromDB, $niksFromExcel);
            $notFoundInDB = array_diff($niksFromExcel, $niksFromDB);

            return response()->json([
                'total_karyawan_excel' => count($niksFromExcel),
                'total_karyawan_db' => count($niksFromDB),
                'nik_not_found_in_excel' => array_values($notFoundInExcel),
                'nik_not_found_in_db' => array_values($notFoundInDB)
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function cekUnitKerja(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('jadwal_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $unitsFromExcelOriginal = [];
            $unitsFromExcelNormalized = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cell = $row->getCellIterator('E', 'E'); // hanya kolom E
                $cell->setIterateOnlyExistingCells(true);
                foreach ($cell as $c) {
                    $unitOriginal = trim($c->getValue());
                    if (!empty($unitOriginal)) {
                        $normalized = strtolower($unitOriginal);
                        $unitsFromExcelNormalized[$normalized] = $unitOriginal;
                    }
                }
            }

            $unitsFromDB = UnitKerja::withoutTrashed()->pluck('nama_unit')->toArray();
            $unitsFromDBNormalized = [];
            foreach ($unitsFromDB as $unitDb) {
                $normalized = strtolower($unitDb);
                $unitsFromDBNormalized[$normalized] = $unitDb;
            }

            $excelKeys = array_keys($unitsFromExcelNormalized);
            $dbKeys = array_keys($unitsFromDBNormalized);

            $notFoundInExcelKeys = array_diff($dbKeys, $excelKeys);
            $notFoundInDBKeys = array_diff($excelKeys, $dbKeys);

            $notFoundInExcel = array_values(array_map(fn($key) => $unitsFromDBNormalized[$key], $notFoundInExcelKeys));
            $notFoundInDB = array_values(array_map(fn($key) => $unitsFromExcelNormalized[$key], $notFoundInDBKeys));

            return response()->json([
                'total_unit_excel' => count($excelKeys),
                'total_unit_db' => count($dbKeys),
                'unit_not_found_in_excel' => $notFoundInExcel,
                'unit_not_found_in_db' => $notFoundInDB
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function cekAgama(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $agamasFromExcel = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('K', 'K'); // Fokus kolom K
                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $agamaValue = trim($cell->getValue());
                    if (!empty($agamaValue)) {
                        $agamasFromExcel[] = ucwords(strtolower($agamaValue)); // Rapihkan kapitalisasi
                    }
                }
            }

            // Hilangkan duplikat
            $agamasFromExcel = array_unique($agamasFromExcel);

            // Ambil semua label agama dari database
            $agamasFromDB = KategoriAgama::pluck('label')->map(function ($label) {
                return ucwords(strtolower(trim($label))); // Rapihkan juga
            })->toArray();

            // Bandingkan
            $notFoundInExcel = array_diff($agamasFromDB, $agamasFromExcel);
            $notFoundInDB = array_diff($agamasFromExcel, $agamasFromDB);

            return response()->json([
                'total_agama_excel' => count($agamasFromExcel),
                'total_agama_db' => count($agamasFromDB),
                'agama_not_found_in_excel' => array_values($notFoundInExcel),
                'agama_not_found_in_db' => array_values($notFoundInDB),
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function cekGolonganDarah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $golonganDarahFromExcel = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('L', 'L'); // Fokus ke kolom L
                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $golDarahValue = strtoupper(trim($cell->getValue())); // Biasanya golongan darah huruf kapital (A, B, AB, O)
                    if (!empty($golDarahValue)) {
                        $golonganDarahFromExcel[] = $golDarahValue;
                    }
                }
            }

            // Hilangkan duplikat
            $golonganDarahFromExcel = array_unique($golonganDarahFromExcel);

            // Ambil semua label golongan darah dari database
            $golonganDarahFromDB = KategoriDarah::pluck('label')->map(function ($label) {
                return strtoupper(trim($label)); // Samakan kapitalisasinya
            })->toArray();

            // Bandingkan
            $notFoundInExcel = array_diff($golonganDarahFromDB, $golonganDarahFromExcel);
            $notFoundInDB = array_diff($golonganDarahFromExcel, $golonganDarahFromDB);

            return response()->json([
                'total_golongan_darah_excel' => count($golonganDarahFromExcel),
                'total_golongan_darah_db' => count($golonganDarahFromDB),
                'golongan_darah_not_found_in_excel' => array_values($notFoundInExcel),
                'golongan_darah_not_found_in_db' => array_values($notFoundInDB),
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function cekHubunganKeluarga(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $hubunganFromExcel = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('D', 'D'); // Fokus kolom D
                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $hubunganValue = ucwords(strtolower(trim($cell->getValue())));
                    if (!empty($hubunganValue)) {
                        $hubunganFromExcel[] = $hubunganValue;
                    }
                }
            }

            // Hilangkan duplikat
            $hubunganFromExcel = array_unique($hubunganFromExcel);

            // Ini daftar enum hubungan yang valid dari database
            $validHubungan = [
                'Suami',
                'Istri',
                'Anak Ke-1',
                'Anak Ke-2',
                'Anak Ke-3',
                'Anak Ke-4',
                'Anak Ke-5',
                'Bapak',
                'Ibu',
                'Bapak Mertua',
                'Ibu Mertua'
            ];

            // Bandingkan
            $notFoundInEnum = array_diff($hubunganFromExcel, $validHubungan);
            $notExistInExcel = array_diff($validHubungan, $hubunganFromExcel);

            return response()->json([
                'total_hubungan_excel' => count($hubunganFromExcel),
                'total_hubungan_enum' => count($validHubungan),
                'hubungan_not_valid' => array_values($notFoundInEnum),
                'hubungan_not_in_excel' => array_values($notExistInExcel),
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function cekDataPendidikanFromDataKeluarga(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            // Ambil file Excel
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $pendidikanFromExcel = [];

            // Ambil data dari kolom E (kolom pendidikan terakhir)
            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('E', 'E'); // Fokus kolom E
                $cellIterator->setIterateOnlyExistingCells(true);

                foreach ($cellIterator as $cell) {
                    $pendidikanValue = ucwords(strtolower(trim($cell->getValue()))); // Proses untuk format yang konsisten
                    if (!empty($pendidikanValue)) {
                        $pendidikanFromExcel[] = $pendidikanValue; // Simpan ke array
                    }
                }
            }

            // Hilangkan duplikat
            $pendidikanFromExcel = array_unique($pendidikanFromExcel);

            // Proses data pendidikan terakhir dan masukkan ke tabel kategori_pendidikans
            // foreach ($pendidikanFromExcel as $pendidikan) {
            //     // Periksa apakah pendidikan sudah ada, jika belum insert
            //     if (!KategoriPendidikan::where('label', $pendidikan)->exists()) {
            //         KategoriPendidikan::create([
            //             'label' => $pendidikan
            //         ]);
            //     }
            // }

            return response()->json([
                'total_pendidikan_excel' => $pendidikanFromExcel,
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function insertDataKaryawan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        DB::beginTransaction();

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $updatedRecords = 0;

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $nik = null;
                $email = null;
                $no_str = null;
                $created_str = null;
                $masa_berlaku_str = null;
                $no_sip = null;
                $created_sip = null;
                $masa_berlaku_sip = null;
                $tinggi_badan = null;
                $berat_badan = null;

                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    $value = trim($cell->getValue());

                    if ($column === 'A') {
                        $nik = $value;
                    } elseif ($column === 'B') {
                        $email = $value;
                    } elseif ($column === 'C') {
                        $no_str = $value;
                    } elseif ($column === 'D') {
                        $created_str = $this->convertDateFormat($value);
                    } elseif ($column === 'E') {
                        $masa_berlaku_str = ($value === 'Seumur Hidup') ? null : $this->convertDateFormat($value);
                    } elseif ($column === 'F') {
                        $no_sip = $value;
                    } elseif ($column === 'G') {
                        $created_sip = $this->convertDateFormat($value);
                    } elseif ($column === 'H') {
                        $masa_berlaku_sip = $this->convertDateFormat($value);
                    } elseif ($column === 'I') {
                        $tinggi_badan = is_numeric($value) ? (int) $value : null;
                    } elseif ($column === 'J') {
                        $berat_badan = is_numeric($value) ? (int) $value : null;
                    }
                }

                if (!empty($nik)) {
                    $karyawan = DataKaryawan::where('nik', $nik)->first();
                    if ($karyawan) {
                        if (!empty($email)) {
                            // Pastikan email tidak duplikat di database
                            if (!DataKaryawan::where('email', $email)->where('nik', '!=', $nik)->exists()) {
                                $karyawan->email = $email;
                            }
                        }
                        if (!empty($no_str)) {
                            $karyawan->no_str = $no_str;
                        }
                        if (!empty($created_str)) {
                            $karyawan->created_str = $created_str;
                        }
                        if (!empty($masa_berlaku_str)) {
                            $masa_berlaku_str = ($value === 'Seumur Hidup') ? null : $value;
                        }
                        if (!empty($no_sip)) {
                            $karyawan->no_sip = $no_sip;
                        }
                        if (!empty($created_sip)) {
                            $karyawan->created_sip = $created_sip;
                        }
                        if (!empty($masa_berlaku_sip)) {
                            $karyawan->masa_berlaku_sip = $masa_berlaku_sip;
                        }
                        if (!empty($tinggi_badan)) {
                            $karyawan->tinggi_badan = $tinggi_badan;
                        }
                        if (!empty($berat_badan)) {
                            $karyawan->berat_badan = $berat_badan;
                        }

                        if (!is_null($berat_badan) && !is_null($tinggi_badan) && $berat_badan > 0 && $tinggi_badan > 0) {
                            $bmi_result = CalculateBMIHelper::calculateBMI($berat_badan, $tinggi_badan);
                            $bmi_value = $bmi_result['bmi_value'];
                            $bmi_ket = $bmi_result['bmi_ket'];
                        } else {
                            $bmi_value = null;
                            $bmi_ket = 'Data belum lengkap';
                        }

                        $karyawan->bmi_value = $bmi_value;
                        $karyawan->bmi_ket = $bmi_ket;

                        $karyawan->save();
                        $updatedRecords++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "$updatedRecords data(s) updated successfully."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function insertSTRSIP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        DB::beginTransaction();

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $updatedRecords = 0;

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $nik = null;
                $no_str = null;
                $created_str = null;
                $masa_berlaku_str = null;
                $no_sip = null;
                $created_sip = null;
                $masa_berlaku_sip = null;

                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    $value = trim($cell->getValue());

                    if ($column === 'A') {
                        $nik = $value;
                    } elseif ($column === 'B') {
                        $no_str = $value;
                    } elseif ($column === 'C') {
                        $created_str = $this->convertDateFormat($value);
                    } elseif ($column === 'D') {
                        $masa_berlaku_str = ($value === 'Seumur Hidup') ? null : $this->convertDateFormat($value);
                    } elseif ($column === 'E') {
                        $no_sip = $value;
                    } elseif ($column === 'F') {
                        $created_sip = $this->convertDateFormat($value);
                    } elseif ($column === 'G') {
                        $masa_berlaku_sip = $this->convertDateFormat($value);
                    }
                }

                if (!empty($nik)) {
                    $karyawan = DataKaryawan::where('nik', $nik)->first();
                    if ($karyawan) {
                        if (!empty($no_str)) {
                            $karyawan->no_str = $no_str;
                        }
                        if (!empty($created_str)) {
                            $karyawan->created_str = $created_str;
                        }
                        if (!empty($masa_berlaku_str)) {
                            $masa_berlaku_str = ($value === 'Seumur Hidup') ? null : $value;
                        }
                        if (!empty($no_sip)) {
                            $karyawan->no_sip = $no_sip;
                        }
                        if (!empty($created_sip)) {
                            $karyawan->created_sip = $created_sip;
                        }
                        if (!empty($masa_berlaku_sip)) {
                            $karyawan->masa_berlaku_sip = $masa_berlaku_sip;
                        }

                        $karyawan->save();
                        $updatedRecords++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "$updatedRecords data(s) updated successfully."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function insertMasterShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shift_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        try {
            $file = $request->file('shift_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            // Ambil semua unit kerja dari DB dan normalisasi ke lowercase
            $unitKerjaDB = UnitKerja::whereNull('deleted_at')->get();
            $unitMap = [];
            foreach ($unitKerjaDB as $unit) {
                $unitMap[strtolower(trim($unit->nama_unit))] = $unit->id;
            }

            $inserted = [];
            $skipped = [];
            $seenCombinations = [];

            DB::beginTransaction();

            try {
                foreach ($sheet->getRowIterator(2) as $row) {
                    $cells = $row->getCellIterator('A', 'D');
                    $cells->setIterateOnlyExistingCells(true);

                    $rowRaw = [];
                    foreach ($cells as $cell) {
                        $rowRaw[] = trim($cell->getValue());
                    }

                    [$namaShift, $jamFrom, $jamTo, $namaUnit] = $rowRaw;

                    // ✅ Konversi jam setelah parsing
                    $jamFrom = $this->convertTimeFormat($jamFrom);
                    $jamTo = $this->convertTimeFormat($jamTo);

                    // ✅ Update rowData supaya skipped log-nya pakai jam hasil konversi
                    $rowData = [$namaShift, $jamFrom, $jamTo, $namaUnit];

                    if (empty($namaShift) || empty($jamFrom) || empty($jamTo) || empty($namaUnit)) {
                        $skipped[] = [
                            'row' => $row->getRowIndex(),
                            'reason' => 'Ada kolom kosong',
                            'data' => $rowData
                        ];
                        continue;
                    }

                    $unitKey = strtolower($namaUnit);
                    if (!isset($unitMap[$unitKey])) {
                        $skipped[] = [
                            'row' => $row->getRowIndex(),
                            'reason' => 'Nama unit tidak ditemukan di DB',
                            'data' => $rowData
                        ];
                        continue;
                    }

                    $uniqueKey = strtolower($namaShift) . '|' . $jamFrom . '|' . $jamTo . '|' . $unitMap[$unitKey];
                    if (isset($seenCombinations[$uniqueKey]) || $this->isDuplicateInDB($namaShift, $jamFrom, $jamTo, $unitMap[$unitKey])) {
                        // $skipped[] = [
                        //     'row' => $row->getRowIndex(),
                        //     'reason' => 'Duplikat di dalam file atau sudah ada di DB',
                        //     'data' => $rowData
                        // ];
                        continue;
                    }

                    // Tandai sudah pernah dilihat
                    $seenCombinations[$uniqueKey] = true;

                    Shift::create([
                        'nama' => $namaShift,
                        'jam_from' => $jamFrom,
                        'jam_to' => $jamTo,
                        'unit_kerja_id' => $unitMap[$unitKey],
                    ]);

                    $inserted[] = $row->getRowIndex();
                }

                DB::commit(); // Commit semua jika tidak ada error
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'errors' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ]);
            }

            return response()->json([
                'inserted_rows' => $inserted,
                'skipped_rows' => $skipped,
                'message' => 'Import selesai.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function insertDataKeluarga(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }

        DB::beginTransaction();

        try {
            $file = $request->file('karyawan_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $updatedRecords = 0;
            $skippedRows = [];  // Array to store skipped rows

            foreach ($sheet->getRowIterator(2) as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                // Variabel untuk menyimpan data
                $nik = null;
                $namaKeluarga = null;
                $hubungan = null;
                $pendidikan = null;
                $pekerjaan = null;
                $tempatLahir = null;
                $tglLahir = null;
                $jenisKelamin = null;
                $agama = null;
                $kategoriDarah = null;
                $noRm = null;

                // Ambil data dari kolom Excel
                foreach ($cellIterator as $cell) {
                    $column = $cell->getColumn();
                    $value = trim($cell->getValue());

                    if ($column === 'A') {
                        $nik = $value;
                    } elseif ($column === 'C') {
                        $namaKeluarga = $value;
                    } elseif ($column === 'D') {
                        $hubungan = ucwords(strtolower($value)); // Mengubah format hubungan
                    } elseif ($column === 'E') {
                        // Cari pendidikan di kategori_pendidikans
                        $pendidikan = $this->findPendidikanId($value);
                    } elseif ($column === 'F') {
                        $pekerjaan = $value;
                    } elseif ($column === 'H') {
                        $tempatLahir = $value;
                    } elseif ($column === 'I') {
                        $tglLahir = $this->convertDateFormat($value);
                    } elseif ($column === 'J') {
                        // P = 0, L = 1
                        $jenisKelamin = ($value === 'P') ? 0 : 1;
                    } elseif ($column === 'K') {
                        // Cari agama di kategori_agamas
                        $agama = $this->findAgamaId($value);
                    } elseif ($column === 'L') {
                        // Cari kategori darah di kategori_darahs
                        $kategoriDarah = $this->findDarahId($value);
                    } elseif ($column === 'M') {
                        $noRm = $value;
                    }
                }

                // Cek jika 'nama_keluarga' (kolom C) kosong, skip data ini
                if (empty($namaKeluarga)) {
                    // Menyimpan baris yang di-skip
                    $skippedRows[] = $rowIndex + 1;  // Menggunakan $rowIndex + 1 karena iterasi dimulai dari 2
                    continue; // Skip data ini jika nama keluarga kosong
                }

                // Pastikan nik ada dan cari data karyawan
                if (!empty($nik)) {
                    $karyawan = DataKaryawan::where('nik', $nik)->first();

                    if ($karyawan) {
                        // Cek duplikat dan tambahkan data keluarga
                        $existingFamily = DataKeluarga::where('data_karyawan_id', $karyawan->id)
                            ->where('nama_keluarga', $namaKeluarga)
                            ->first();

                        if (!$existingFamily) {
                            DataKeluarga::create([
                                'data_karyawan_id' => $karyawan->id,
                                'nama_keluarga' => $namaKeluarga,
                                'hubungan' => $hubungan,
                                'pendidikan_terakhir' => $pendidikan,
                                'pekerjaan' => $pekerjaan,
                                'tempat_lahir' => $tempatLahir,
                                'tgl_lahir' => $tglLahir,
                                'jenis_kelamin' => $jenisKelamin,
                                'kategori_agama_id' => $agama,
                                'kategori_darah_id' => $kategoriDarah,
                                'no_rm' => $noRm,
                                'status_hidup' => true,
                                'status_keluarga_id' => 2,
                                'is_menikah' => true,
                                'is_bpjs' => false,
                                'verifikator_1' => 1,
                            ]);

                            $updatedRecords++;
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "$updatedRecords data keluarga berhasil ditambahkan.",
                'skipped_rows' => $skippedRows,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    // TODO: Insert karyawan doktor mitra

    private function findPendidikanId($pendidikanLabel)
    {
        $pendidikan = KategoriPendidikan::where('label', $pendidikanLabel)->first();
        return $pendidikan ? $pendidikan->id : null;
    }

    private function findAgamaId($agamaLabel)
    {
        $agama = KategoriAgama::where('label', $agamaLabel)->first();
        return $agama ? $agama->id : null;
    }

    private function findDarahId($darahLabel)
    {
        $darah = KategoriDarah::where('label', $darahLabel)->first();
        return $darah ? $darah->id : null;
    }

    private function convertDateFormat($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (is_numeric($date)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)
                )->format('d-m-Y');
            }

            return Carbon::createFromFormat('d-m-Y', $date)->format('d-m-Y');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/Y', $date)->format('d-m-Y');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    private function convertTimeFormat($value): ?string
    {
        if (is_numeric($value)) {
            try {
                $timestamp = Date::excelToDateTimeObject($value);
                return $timestamp->format('H:i:s');
            } catch (\Exception $e) {
                return null; // fallback jika gagal konversi
            }
        }

        if (is_string($value)) {
            return str_replace('.', ':', $value);
        }

        return null;
    }

    private function isDuplicateInDB(string $nama, string $jamFrom, string $jamTo, int $unitKerjaId): bool
    {
        return Shift::where('nama', $nama)
            ->where('jam_from', $jamFrom)
            ->where('jam_to', $jamTo)
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereNull('deleted_at')
            ->exists();
    }
}

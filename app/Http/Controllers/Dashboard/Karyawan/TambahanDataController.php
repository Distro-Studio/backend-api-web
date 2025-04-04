<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Helpers\CalculateBMIHelper;
use App\Http\Controllers\Controller;
use App\Models\DataKaryawan;
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

            foreach ($sheet->getRowIterator(2) as $row) {
                $cell = $row->getCellIterator('A');
                $cell->setIterateOnlyExistingCells(true);
                foreach ($cell as $c) {
                    $nikValue = trim($c->getValue());
                    if (!empty($nikValue)) {
                        $niksFromExcel[] = $nikValue;
                    }
                }
            }

            $niksFromDB = DataKaryawan::where('nik', '!=', null)->pluck('nik')->toArray();

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

    private function convertDateFormat($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (is_numeric($date)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)
                )->format('Y-m-d H:i:s');
            }

            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d H:i:s');
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

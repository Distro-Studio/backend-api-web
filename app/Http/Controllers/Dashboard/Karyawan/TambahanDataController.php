<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\DataKaryawan;
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
}

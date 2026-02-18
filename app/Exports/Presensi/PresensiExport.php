<?php

namespace App\Exports\Presensi;

use App\Exports\Sheet\PresensiSheet;
use App\Exports\Presensi\PresensiUserSheet;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PresensiExport implements WithMultipleSheets
{
    use Exportable;

    private $startDate;
    private $endDate;
    private $filters;

    public function __construct($startDate, $endDate, $filters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Jika ada filter user_id, buat satu sheet per karyawan (nama sheet = nama karyawan)
        if (isset($this->filters['user_id'])) {
            $userFilter = $this->filters['user_id'];
            $userIds = is_array($userFilter) ? $userFilter : [$userFilter];
            $users = User::whereIn('id', $userIds)->get();
            foreach ($users as $user) {
                $sheets[] = new PresensiUserSheet($user, $this->startDate, $this->endDate, $this->filters);
            }
            return $sheets;
        }

        // Menambahkan sheet untuk setiap kategori presensi (default)
        $categories = ['Terlambat', 'Tepat Waktu', 'Alpha'];
        foreach ($categories as $category) {
            $sheets[] = new PresensiSheet($category, 'Laporan ' . str_replace(' ', '', $category), $this->startDate, $this->endDate, $this->filters);
        }

        return $sheets;
    }
}

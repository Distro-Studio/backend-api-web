<?php

namespace App\Exports\Pengaturan\Akun;

use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class RolesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        if (!empty($this->ids)) {
            return Role::whereIn('id', $this->ids)->get();
        }
        return Role::all();
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'created_at',
            'updated_at',
        ];
    }

    public function map($roles): array
    {
        return [
            $roles->name,
            $roles->description,
            Carbon::parse($roles->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($roles->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}

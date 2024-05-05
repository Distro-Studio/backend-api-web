<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitKerjaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_unit' => 'required|string|max:225',
            'jenis_karyawan' => 'string|max:225',
        ];
    }

    public function messages()
    {
        return [
            'nama_unit.required' => 'Nama Unit Kerja tidak diperbolehkan kosong.',
            'nama_unit.string' => 'Nama Unit Kerja tidak diperbolehkan mengandung angka.',
            'nama_unit.max' => 'Nama Unit Kerja melebihi batas maksimum panjang karakter.',
            'nama_unit.unique' => 'Nama Unit Kerja tersebut sudah pernah dibuat.',
            // 'jenis_karyawan.required' => 'Jenis Karyawan tidak diperbolehkan kosong.',
            'jenis_karyawan.string' => 'Jenis Karyawan tidak diperbolehkan mengandung angka.',
            'jenis_karyawan.max' => 'Jenis Karyawan melebihi batas maksimum panjang karakter.',
        ];
    }
}

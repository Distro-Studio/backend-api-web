<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKelompokGajiRequest extends FormRequest
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
            'nama_kelompok' => 'required|string|max:225|unique:kelompok_gajis,nama_kelompok',
            'besaran_gaji' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'nama_kelompok.required' => 'Kode Kelompok Gaji tidak diperbolehkan kosong.',
            'nama_kelompok.string' => 'Kode Kelompok Gaji tidak diperbolehkan mengandung angka.',
            'nama_kelompok.unique' => 'Kode Kelompok Gaji tersebut sudah pernah dibuat.',
            'nama_kelompok.max' => 'Kode Kelompok Gaji melebihi batas maksimum panjang karakter.',
            'besaran_gaji.required' => 'Jumlah Gaji tidak diperbolehkan kosong.',
            'besaran_gaji.numeric' => 'Jumlah Gaji tidak diperbolehkan mengandung huruf.',
        ];
    }
}

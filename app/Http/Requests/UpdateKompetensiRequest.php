<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKompetensiRequest extends FormRequest
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
            'nama_kompetensi' => 'required|string|max:225',
            'jenis_kompetensi' => 'required|string|max:225',
            'total_tunjangan' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'nama_kompetensi.required' => 'Nama Kompetensi tidak diperbolehkan kosong.',
            'nama_kompetensi.string' => 'Nama Kompetensi tidak diperbolehkan mengandung angka.',
            'nama_kompetensi.max' => 'Nama Kompetensi melebihi batas maksimum panjang karakter.',
            'nama_kompetensi.unique' => 'Nama Kompetensi tersebut sudah pernah dibuat.',
            'jenis_kompetensi.required' => 'Jenis Kompetensi tidak diperbolehkan kosong.',
            'jenis_kompetensi.string' => 'Jenis Kompetensi tidak diperbolehkan mengandung angka.',
            'jenis_kompetensi.max' => 'Jenis Kompetensi melebihi batas maksimum panjang karakter.',
            'total_tunjangan.required' => 'Jumlah Tunjangan tidak diperbolehkan kosong.',
            'total_tunjangan.numeric' => 'Jumlah Tunjangan tidak diperbolehkan mengandung huruf.',
        ];
    }
}

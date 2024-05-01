<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
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
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'is_struktural' => 'boolean',
            'tunjangan' => 'nullable|numeric'
        ];
    }

    public function messages()
    {
        return [
            'nama_jabatan.required' => 'Nama Jabatan tidak diperbolehkan kosong.',
            'nama_jabatan.string' => 'Nama Jabatan tidak diperbolehkan mengandung angka.',
            'nama_jabatan.unique' => 'Nama Jabatan tersebut sudah pernah dibuat.',
            'nama_jabatan.max' => 'Nama Jabatan melebihi batas maksimum panjang karakter.',
            'tunjangan.numeric' => 'Tunjangan hanya diperbolehkan berisi angka.',
        ];
    }
}

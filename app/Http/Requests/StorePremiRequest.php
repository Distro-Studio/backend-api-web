<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;

class StorePremiRequest extends FormRequest
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
            'nama_premi' => 'required|string|max:225|unique:premis,nama_premi',
            'jenis_premi' => 'required',
            'besaran_premi' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'nama_premi.required' => 'Nama Premi tidak diperbolehkan kosong.',
            'nama_premi.string' => 'Nama Premi tidak diperbolehkan mengandung angka.',
            'nama_premi.max' => 'Nama Premi melebihi batas maksimum panjang karakter.',
            'nama_premi.unique' => 'Nama Premi tersebut sudah pernah dibuat.',
            'jenis_premi.required' => 'Silahkan pilih jenis premi terlebih dahulu.',
            'besaran_premi.required' => 'Jumlah Premi tidak diperbolehkan kosong.',
            'besaran_premi.numeric' => 'Jumlah Premi tidak diperbolehkan mengandung huruf.',
        ];
    }
}

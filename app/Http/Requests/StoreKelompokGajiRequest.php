<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreKelompokGajiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'nama_kelompok' => 'required|string|max:10|unique:kelompok_gajis,nama_kelompok',
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

    public function failedValidation(Validator $validator)
    {
        $reponse = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($reponse, Response::HTTP_BAD_REQUEST));
    }
}

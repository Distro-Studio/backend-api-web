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
            'nama_kelompok.required' => 'Kode kelompok gaji tidak diperbolehkan kosong.',
            'nama_kelompok.string' => 'Kode kelompok gaji tidak diperbolehkan mengandung angka.',
            'nama_kelompok.unique' => 'Kode kelompok gaji tersebut sudah pernah dibuat.',
            'nama_kelompok.max' => 'Kode kelompok gaji melebihi batas maksimum panjang karakter.',
            'besaran_gaji.required' => 'Jumlah gaji tidak diperbolehkan kosong.',
            'besaran_gaji.numeric' => 'Jumlah gaji tidak diperbolehkan mengandung huruf.',
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

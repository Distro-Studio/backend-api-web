<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'nama_kelompok' => 'required|string|max:225',
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

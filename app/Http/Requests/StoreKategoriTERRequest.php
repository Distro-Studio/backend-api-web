<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreKategoriTERRequest extends FormRequest
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
            'nama_kategori_ter' => 'required|string|max:225|unique:kategori_ters,nama_kategori_ter',
        ];
    }

    public function messages()
    {
        return [
            'nama_kategori_ter.required' => 'Nama kategori TER tidak diperbolehkan kosong.',
            'nama_kategori_ter.string' => 'Nama kategori TER tidak diperbolehkan mengandung angka.',
            'nama_kategori_ter.max' => 'Nama kategori TER melebihi batas maksimum panjang karakter.',
            'nama_kategori_ter.unique' => 'Nama kategori TER tersebut sudah pernah dibuat.',
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

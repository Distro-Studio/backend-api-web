<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJabatanRequest extends FormRequest
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
            'is_struktural' => 'required|boolean',
            'tunjangan_jabatan' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'nama_jabatan.required' => 'Nama jabatan tidak diperbolehkan kosong.',
            'nama_jabatan.string' => 'Nama jabatan tidak diperbolehkan mengandung angka.',
            'nama_jabatan.unique' => 'Nama jabatan tersebut sudah pernah dibuat.',
            'nama_jabatan.max' => 'Nama jabatan melebihi batas maksimum panjang karakter.',
            'is_struktural.required' => 'Jenis jabatan tidak diperbolehkan kosong.',
            'tunjangan_jabatan.required' => 'Jumlah tunjangan jabatan tidak diperbolehkan kosong.',
            'tunjangan_jabatan.numeric' => 'Tunjangan jabatan hanya diperbolehkan berisi angka.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages,
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'nama_jabatan' => 'required|string|max:255',
            'is_struktural' => 'boolean',
            'tunjangan' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'nama_jabatan.required' => 'Nama Jabatan tidak diperbolehkan kosong.',
            'nama_jabatan.string' => 'Nama Jabatan tidak diperbolehkan mengandung angka.',
            'nama_jabatan.unique' => 'Nama Jabatan tersebut sudah pernah dibuat.',
            'nama_jabatan.max' => 'Nama Jabatan melebihi batas maksimum panjang karakter.',
            'tunjangan.required' => 'Jumlah Tunjangan tidak diperbolehkan kosong.',
            'tunjangan.numeric' => 'Tunjangan hanya diperbolehkan berisi angka.',
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

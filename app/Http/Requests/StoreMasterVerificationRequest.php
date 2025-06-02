<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMasterVerificationRequest extends FormRequest
{
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
            'nama' => 'required|string',
            'verifikator' => 'required|integer|exists:users,id',
            'modul_verifikasi' => 'required|integer|exists:modul_verifikasis,id',
            'order' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama master verifikasi tidak diperbolehkan kosong.',
            'nama.string' => 'Nama master verifikasi tidak diperbolehkan mengandung selain angka dan huruf.',
            'verifikator.required' => 'Verifikator verifikasi tidak diperbolehkan kosong.',
            'verifikator.integer' => 'Verifikator verifikasi tidak diperbolehkan mengandung huruf.',
            'verifikator.exists' => 'Verifikator verifikasi tersebut tidak valid.',
            'modul_verifikasi.required' => 'Jenis verifikasi tidak diperbolehkan kosong.',
            'modul_verifikasi.integer' => 'Jenis verifikasi tidak diperbolehkan mengandung huruf.',
            'modul_verifikasi.exists' => 'Jenis verifikasi tersebut tidak valid.',
            'order.required' => 'Urutan verifikasi tidak diperbolehkan kosong.',
            'order.numeric' => 'Urutan verifikasi tidak diperbolehkan mengandung huruf.'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $reponse = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages
        ];

        throw new HttpResponseException(response()->json($reponse, Response::HTTP_BAD_REQUEST));
    }
}

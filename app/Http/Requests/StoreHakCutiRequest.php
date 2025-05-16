<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHakCutiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_karyawan_id' => 'required|exists:data_karyawans,id',
            'tipe_cuti_id' => 'required|array|min:1',
            'tipe_cuti_id.*' => 'required|exists:tipe_cutis,id',
        ];
    }

    public function messages()
    {
        return [
            'data_karyawan_id.required' => 'Data karyawan tidak boleh kosong.',
            'data_karyawan_id.exists' => 'Data karyawan tidak ditemukan.',
            'tipe_cuti_id.required' => 'Tipe cuti tidak boleh kosong.',
            'tipe_cuti_id.array' => 'Tipe cuti harus berupa array.',
            'tipe_cuti_id.*.required' => 'Tipe cuti tidak boleh kosong.',
            'tipe_cuti_id.*.exists' => 'Terdapat salah satu tipe cuti yang tidak valid.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

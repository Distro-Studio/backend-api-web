<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateHakCutiRequest extends FormRequest
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
            'hak_cuti' => 'required|array|min:1',
            'hak_cuti.*.id' => 'required|exists:tipe_cutis,id',
            'hak_cuti.*.kuota' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'hak_cuti.required' => 'Data hak cuti tidak boleh kosong.',
            'hak_cuti.array' => 'Data hak cuti harus berupa array.',
            'hak_cuti.*.id.required' => 'Tipe cuti wajib diisi.',
            'hak_cuti.*.id.exists' => 'Terdapat tipe cuti yang tidak valid.',
            'hak_cuti.*.kuota.required' => 'Kuota wajib diisi.',
            'hak_cuti.*.kuota.integer' => 'Kuota harus berupa angka.',
            'hak_cuti.*.kuota.min' => 'Kuota minimal adalah 0.',
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

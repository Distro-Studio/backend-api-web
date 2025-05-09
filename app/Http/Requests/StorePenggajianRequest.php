<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenggajianRequest extends FormRequest
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
            'data_karyawan_ids' => 'required|array|min:1',
            'data_karyawan_ids.*' => 'integer|exists:data_karyawans,id'
        ];
    }

    public function messages()
    {
        return [
            'data_karyawan_ids.required' => 'Silahkan pilih karyawan terlebih dahulu.',
            'data_karyawan_ids.array' => 'Karyawan harus berupa array.',
            'data_karyawan_ids.min' => 'Silahkan pilih salah satu karyawan terlebih dahulu.',
            'data_karyawan_ids.*.integer' => 'ID karyawan harus berupa integer.',
            'data_karyawan_ids.*.exists' => 'ID karyawan tidak ditemukan.'
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

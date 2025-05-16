<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreSpesialisasiRequest extends FormRequest
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
            'nama_spesialisasi' => 'required|string|max:225|unique:spesialisasis,nama_spesialisasi',
        ];
    }

    public function messages()
    {
        return [
            'nama_spesialisasi.required' => 'Nama spesialisasi tidak diperbolehkan kosong.',
            'nama_spesialisasi.max' => 'Nama spesialisasi melebihi batas maksimum panjang karakter.',
            'nama_spesialisasi.unique' => 'Nama spesialisasi tersebut sudah pernah dibuat.'
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

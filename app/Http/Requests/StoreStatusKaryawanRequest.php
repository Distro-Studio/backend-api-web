<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStatusKaryawanRequest extends FormRequest
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
            'label' => 'required|string|max:255|unique:status_karyawans,label',
            'kategori_status_id' => 'required|exists:kategori_status_karyawans,id'
        ];
    }

    public function messages()
    {
        return [
            'label.required' => 'Nama status karyawan tidak diperbolehkan kosong.',
            'label.string' => 'Nama status karyawan tidak diperbolehkan mengandung angka.',
            'label.unique' => 'Nama status karyawan tersebut sudah pernah dibuat.',
            'label.max' => 'Nama status karyawan melebihi batas maksimum panjang karakter.',
            'kategori_status_id.required' => 'Kategori status karyawan tidak diperbolehkan kosong.',
            'kategori_status_id.exists' => 'Kategori status karyawan tidak ditemukan.',
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

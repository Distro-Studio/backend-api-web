<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNonShiftRequest extends FormRequest
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
            'nama' => 'required|string|max:225|unique:non_shifts,nama',
            'jam_from' => 'required|string',
            'jam_to' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama jam kerja tetap tidak diperbolehkan kosong.',
            'nama.string' => 'Nama jam kerja tetap tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama jam kerja tetap melebihi batas maksimum panjang karakter.',
            'nama.unique' => 'Nama jam kerja tetap tersebut sudah pernah dibuat.',
            'jam_from.required' => 'Jam kerja mulai jam kerja tetap tidak diperbolehkan kosong.',
            'jam_from.string' => 'Jam kerja mulai jam kerja tetap tidak diperbolehkan mengandung angka.',
            'jam_to.required' => 'Jam kerja selesai jam kerja tetap tidak diperbolehkan kosong.',
            'jam_to.string' => 'Jam kerja selesai jam kerja tetap tidak diperbolehkan mengandung angka.',
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

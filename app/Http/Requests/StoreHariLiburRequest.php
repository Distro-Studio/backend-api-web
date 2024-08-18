<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHariLiburRequest extends FormRequest
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
            'nama' => 'required|string|max:225',
            'tanggal' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama hari libur tidak diperbolehkan kosong.',
            'nama.string' => 'Nama hari libur tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama hari libur melebihi batas maksimum panjang karakter.',
            'tanggal.required' => 'Tanggal hari libur tidak diperbolehkan kosong.',
            'tanggal.string' => 'Tanggal hari libur tidak diperbolehkan mengandung selain angka dan huruf.',
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

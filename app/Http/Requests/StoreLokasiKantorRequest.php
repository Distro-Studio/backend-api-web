<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLokasiKantorRequest extends FormRequest
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
            'alamat' => 'required',
            'lat' => 'required|string',
            'long' => 'required|string',
            'radius' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'alamat.required' => 'Alamat kantor tidak diperbolehkan kosong.',
            'lat.required' => 'Titik lokasi latitude kantor tidak diperbolehkan kosong.',
            'long.required' => 'Titik lokasi longitude kantor tidak diperbolehkan kosong.',
            'radius.required' => 'Lebar radius presensi kantor tidak diperbolehkan kosong.',
            'radius.numeric' => 'Lebar radius presensi kantor tidak diperbolehkan mengandung karakter selain angka.',
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

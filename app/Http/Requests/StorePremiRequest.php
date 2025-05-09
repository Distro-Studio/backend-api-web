<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePremiRequest extends FormRequest
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
            'nama_premi' => 'required|string|unique:premis,nama_premi',
            'kategori_potongan_id' => 'required|integer|exists:kategori_potongans,id',
            'jenis_premi' => 'required',
            'besaran_premi' => 'required|numeric',
            'minimal_rate' => 'nullable|integer',
            'maksimal_rate' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'nama_premi.required' => 'Nama premi tidak diperbolehkan kosong.',
            'nama_premi.string' => 'Nama premi tidak diperbolehkan mengandung angka.',
            'nama_premi.unique' => 'Nama premi pada tabel excel atau database sudah pernah dibuat atau terduplikat.',
            'kategori_potongan_id.required' => 'Sumber potongan premi tidak diperbolehkan kosong.',
            'kategori_potongan_id.integer' => 'Sumber potongan premi tidak diperbolehkan mengandung huruf.',
            'kategori_potongan_id.exists' => 'Sumber potongan premi tersebut tidak valid.',
            'jenis_premi.required' => 'Silahkan pilih jenis premi terlebih dahulu.',
            'besaran_premi.required' => 'Jumlah premi tidak diperbolehkan kosong.',
            'besaran_premi.numeric' => 'Jumlah premi tidak diperbolehkan mengandung huruf.',
            'minimal_rate.integer' => 'Minimal rate tidak diperbolehkan mengandung huruf.',
            'maksimal_rate.integer' => 'Maksimal rate tidak diperbolehkan mengandung huruf.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTagihanPotonganRequest extends FormRequest
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
            'kategori_tagihan_id' => 'required|integer|exists:kategori_tagihan_potongans,id',
            'besaran' => 'required|numeric',
            'bulan_mulai' => 'nullable|string',
            'bulan_selesai' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'kategori_tagihan_id.required' => 'Kategori tagihan potongan tidak diperbolehkan kosong.',
            'kategori_tagihan_id.integer' => 'Kategori tagihan potongan tidak diperbolehkan mengandung angka.',
            'kategori_tagihan_id.exists' => 'Kategori tagihan potongan yang dipilih tidak valid.',
            'besaran.required' => 'Besaran pengurang gaji tidak diperbolehkan kosong.',
            'besaran.numeric' => 'Besaran pengurang gaji tidak diperbolehkan mengandung huruf.',
            'bulan_mulai.string' => 'Data tanggal mulai hanya diperbolehkan mengandung huruf dan angka.',
            'bulan_selesai.string' => 'Data tanggal selesai hanya diperbolehkan mengandung huruf dan angka.',
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

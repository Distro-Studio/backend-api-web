<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenyesuaianGajiCustomRequest extends FormRequest
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
            'nama_detail' => 'required|string',
            'besaran' => 'required|numeric',
            'bulan_mulai' => 'nullable|date',
            'bulan_selesai' => 'nullable|date|after_or_equal:bulan_mulai',
        ];
    }

    public function messages()
    {
        return [
            'nama_detail.required' => 'Nama pengurang gaji tidak diperbolehkan kosong.',
            'nama_detail.string' => 'Nama pengurang gaji tidak diperbolehkan mengandung angka.',
            'besaran.required' => 'Besaran pengurang gaji tidak diperbolehkan kosong.',
            'besaran.numeric' => 'Besaran pengurang gaji tidak diperbolehkan mengandung huruf.',
            'bulan_mulai.date' => 'Data tanggal mulai yang valid adalah berupa tanggal.',
            'bulan_selesai.date' => 'Data tanggal selesai yang valid adalah berupa tanggal.',
            'bulan_selesai.after_or_equal' => 'Bulan selesai harus setelah atau sama dengan bulan mulai.'
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

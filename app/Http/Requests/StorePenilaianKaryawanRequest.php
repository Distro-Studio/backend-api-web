<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenilaianKaryawanRequest extends FormRequest
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
            'user_dinilai' => 'required|integer|exists:users,id',
            // 'user_penilai' => 'required|integer|exists:users,id',
            'jenis_penilaian_id' => 'required|integer|exists:jenis_penilaian,id',
            'pertanyaan_jawaban' => 'required|string',
            'total_pertanyaan' => 'required|numeric',
            'rata_rata' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'user_dinilai.required' => 'Silahkan pilih karyawan yang ingin dinilai terlebih dahulu.',
            'user_dinilai.integer' => 'Data karyawan yang valid adalah berupa satuan angka.',
            'user_dinilai.exists' => 'Data karyawan yang dipilih tidak tersedia.',
            'user_penilai.required' => 'Silahkan pilih karyawan yang menilai terlebih dahulu.',
            'user_penilai.integer' => 'Data yang valid adalah berupa satuan angka.',
            'user_penilai.exists' => 'Data karyawan yang dipilih tidak tersedia.',
            'jenis_penilaian_id.required' => 'Silahkan pilih jenis penilaian yang tersedia terlebih dahulu.',
            'jenis_penilaian_id.integer' => 'Data yang valid adalah berupa satuan angka.',
            'jenis_penilaian_id.exists' => 'Data jenis penilaian yang dipilih tidak tersedia.',
            'pertanyaan_jawaban.required' => 'Data pertanyaan jawaban tidak diperbolehkan kosong.',
            'pertanyaan_jawaban.string' => 'Data pertanyaan jawaban yang valid adalah berupa teks.',
            'total_pertanyaan.required' => 'Data total pertanyaan tidak diperbolehkan kosong.',
            'total_pertanyaan.numeric' => 'Data total pertanyaan yang valid adalah berupa angka.',
            'rata_rata.required' => 'Data rata-rata tidak diperbolehkan kosong.',
            'rata_rata.numeric' => 'Data rata-rata yang valid adalah berupa angka.',
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

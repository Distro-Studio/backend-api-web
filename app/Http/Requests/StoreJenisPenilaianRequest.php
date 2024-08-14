<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJenisPenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|unique:jenis_penilaians,nama',
            'status_karyawan_id' => 'required|integer|exists:status_karyawans,id',
            'jabatan_penilai' => 'required|integer|exists:jabatans,id',
            'jabatan_dinilai' => 'required|integer|exists:jabatans,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Data nama jenis penilaian tidak diperbolehkan kosong.',
            'nama.string' => 'Data nama jenis penilaian yang valid adalah berupa satuan angka dan teks.',
            'nama.unique' => 'Data nama jenis penilaian yang diberikan sudah pernah dibuat.',
            'status_karyawan_id.required' => 'Silahkan pilih status kepegawaian yang tersedia terlebih dahulu.',
            'status_karyawan_id.integer' => 'Data status kepegawaian yang valid adalah berupa satuan angka.',
            'status_karyawan_id.exists' => 'Data status kepegawaian yang terdipilih tidak tersedia.',
            'jabatan_penilai.required' => 'Silahkan pilih jabatan karyawan yang menilai terlebih dahulu.',
            'jabatan_penilai.integer' => 'Data jabatan yang valid adalah berupa satuan angka.',
            'jabatan_penilai.exists' => 'Data jabatan yang dipilih tidak tersedia.',
            'jabatan_dinilai.required' => 'Silahkan pilih jabatan karyawan yang ingin dinilai terlebih dahulu.',
            'jabatan_dinilai.integer' => 'Data jabatan yang valid adalah berupa satuan angka.',
            'jabatan_dinilai.exists' => 'Data jabatan yang dipilih tidak tersedia.',
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

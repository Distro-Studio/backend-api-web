<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDataKeluargaReqeust extends FormRequest
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
            'nama_keluarga' => 'required|string|max:255',
            'hubungan' => 'required',
            'pendidikan_terakhir' => 'required|integer|exists:kategori_pendidikans,id',
            'status_hidup' => 'required|boolean',
            'pekerjaan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|numeric',
            'email' => 'nullable|email|max:255',
            'is_bpjs' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'nama_keluarga.required' => 'Nama anggota keluarga harus diisi.',
            'nama_keluarga.string' => 'Nama anggota keluarga harus berupa text.',
            'nama_keluarga.max' => 'Nama anggota keluarga maksimal 255 karakter.',
            'hubungan.required' => 'Hubungan keluarga harus diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir harus diisi.',
            'pendidikan_terakhir.integer' => 'Pendidikan terakhir harus berupa angka.',
            'pendidikan_terakhir.exists' => 'Pendidikan terakhir tidak ditemukan dalam daftar yang valid.',
            'status_hidup.required' => 'Status hidup harus diisi.',
            'status_hidup.boolean' => 'Status hidup harus berupa true atau false.',
            'pekerjaan.string' => 'Pekerjaan harus berupa string.',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter.',
            'no_hp.numeric' => 'Nomor HP harus berupa angka.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'is_bpjs.required' => 'Status BPJS harus diisi.',
            'is_bpjs.boolean' => 'Status BPJS harus berupa true atau false.',
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

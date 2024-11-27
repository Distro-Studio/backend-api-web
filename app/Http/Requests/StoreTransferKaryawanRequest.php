<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransferKaryawanRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'tgl_mulai' => 'required|string',
            'unit_kerja_tujuan' => 'nullable|integer|exists:unit_kerjas,id',
            'jabatan_tujuan' => 'nullable|integer|exists:jabatans,id',
            'kelompok_gaji_tujuan' => 'nullable|integer|exists:kelompok_gajis,id',
            'role_tujuan' => 'nullable|integer|exists:roles,id',
            'kategori_transfer_id' => 'required|integer|exists:kategori_transfer_karyawans,id',
            'alasan' => 'required|string',
            'dokumen' => 'required|file|max:10240|mimes:pdf',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            'tgl_mulai.required' => 'Silahkan masukkan tanggal mulai kerja terlebih dahulu.',
            'tgl_mulai.string' => 'Data tanggal mulai kerja tidak diperbolehkan mengandung selain angka dan huruf.',
            'unit_kerja_tujuan.integer' => 'Data tujuan unit kerja karyawan yang valid adalah berupa satuan angka.',
            'unit_kerja_tujuan.exists' => 'Unit kerja karyawan yang dituju tidak valid.',
            'jabatan_tujuan.integer' => 'Data tujuan jabatan karyawan yang valid adalah berupa satuan angka.',
            'jabatan_tujuan.exists' => 'Jabatan karyawan yang dituju tidak valid.',
            'kelompok_gaji_tujuan.integer' => 'Data tujuan kelompok gaji karyawan yang valid adalah berupa satuan angka.',
            'kelompok_gaji_tujuan.exists' => 'Kelompok gaji karyawan yang dituju tidak valid.',
            'role_tujuan.integer' => 'Data tujuan role karyawan yang valid adalah berupa satuan angka.',
            'role_tujuan.exists' => 'Role karyawan yang dituju tidak valid.',
            'kategori_transfer_id.required' => 'Silahkan pilih kategori transfer yang tersedia terlebih dahulu.',
            'kategori_transfer_id.integer' => 'Kategori transfer karyawan yang valid adalah berupa satuan angka.',
            'kategori_transfer_id.exists' => 'Kategori transfer karyawan yang dipilih tidak valid.',
            'alasan.required' => 'Alasan transfer karyawan tidak diperbolehkan kosong.',
            'alasan.string' => 'Alasan transfer karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'dokumen.required' => 'Dokumen transfer karyawan tidak diperbolehkan kosong.',
            'dokumen.file' => 'Dokumen yang diperbolehkan berupa berkas file .PDF',
            'dokumen.mimes' => 'Dokumen yang diperbolehkan berupa berkas file .PDF',
            'dokumen.max' => 'Dokumen yang diunggah harus kurang dari 10 MB.',
            // 'role_id.integer' => 'Data role yang valid adalah berupa satuan angka.',
            // 'role_id.exists' => 'Role yang dipilih tidak valid.',
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

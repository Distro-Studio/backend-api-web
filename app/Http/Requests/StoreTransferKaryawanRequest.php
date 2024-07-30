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
            'tgl_mulai' => 'required|date',
            'unit_kerja_asal' => 'required|integer|exists:unit_kerjas,id',
            'unit_kerja_tujuan' => 'required|integer|exists:unit_kerjas,id',
            'jabatan_asal' => 'required|integer|exists:jabatans,id',
            'jabatan_tujuan' => 'required|integer|exists:jabatans,id',
            'kategori_transfer_id' => 'required|integer|exists:kategori_transfer_karyawans,id',
            'alasan' => 'required|string',
            'dokumen' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            'tgl_mulai.required' => 'Silahkan masukkan tanggal mulai kerja terlebih dahulu.',
            'tgl_mulai.date' => 'Data tanggal mulai kerja yang valid adalah berupa tanggal dan waktu.',
            'unit_kerja_asal.integer' => 'Data asal unit kerja karyawan sebelumnya adalah berupa satuan angka.',
            'unit_kerja_asal.required' => 'Data asal unit kerja karyawan otomatis akan terisi jika karyawan terpilih.',
            'unit_kerja_asal.exists' => 'Unit kerja karyawan asal yang dipilih tidak valid.',
            'unit_kerja_tujuan.required' => 'Silahkan pilih tujuan unit kerja karyawan yang tersedia terlebih dahulu.',
            'unit_kerja_tujuan.integer' => 'Data tujuan unit kerja karyawan yang valid adalah berupa satuan angka.',
            'unit_kerja_tujuan.exists' => 'Unit kerja karyawan yang dituju tidak valid.',
            'jabatan_asal.integer' => 'Data jabatan karyawan sebelumnya adalah berupa satuan angka.',
            'jabatan_asal.required' => 'Data asal jabatan karyawan otomatis akan terisi jika karyawan terpilih.',
            'jabatan_asal.exists' => 'Jabatan karyawan asal yang dipilih tidak valid.',
            'jabatan_tujuan.required' => 'Silahkan pilih tujuan jabatan karyawan yang tersedia terlebih dahulu.',
            'jabatan_tujuan.integer' => 'Data tujuan jabatan karyawan yang valid adalah berupa satuan angka.',
            'jabatan_tujuan.exists' => 'Jabatan karyawan yang dituju tidak valid.',
            'kategori_transfer_id.required' => 'Silahkan pilih kategori transfer yang tersedia terlebih dahulu.',
            'kategori_transfer_id.integer' => 'Kategori transfer karyawan yang valid adalah berupa satuan angka.',
            'kategori_transfer_id.exists' => 'Kategori transfer karyawan yang dipilih tidak valid.',
            'alasan.required' => 'Alasan transfer karyawan tidak diperbolehkan kosong.',
            'alasan.string' => 'Alasan transfer karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'dokumen.required' => 'Dokumen transfer karyawan tidak diperbolehkan kosong.',
            'dokumen.file' => 'Dokumen yang diperbolehkan berupa berkas file .PDF, .XLS, .XLSX, .DOC, dan .DOCX',
            'dokumen.mimes' => 'Dokumen yang diperbolehkan berupa berkas file .PDF, .XLS, .XLSX, .DOC, dan .DOCX',
            'dokumen.max' => 'Dokumen yang diunggah harus kurang dari 10 MB.',
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

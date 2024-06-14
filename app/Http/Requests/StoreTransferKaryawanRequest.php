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
            'user_id' => 'required|integer',
            'tgl_mulai' => 'required|date',
            'tipe' => 'required|string',
            'unit_kerja_asal' => 'required|integer',
            'unit_kerja_tujuan' => 'required|integer',
            'jabatan_asal' => 'required|integer',
            'jabatan_tujuan' => 'required|integer',
            'alasan' => 'required|string',
            'dokumen' => 'required|file|max:2048|mimes:pdf,doc,docx,xls,xlsx',

        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            'tgl_mulai.required' => 'Silahkan masukkan tanggal mulai kerja terlebih dahulu.',
            'tgl_mulai.date' => 'Data tanggal mulai kerja yang valid adalah berupa tanggal dan waktu.',
            'tipe.required' => 'Silahkan pilih tipe transfer yang tersedia terlebih dahulu.',
            'tipe.string' => 'Tipe transfer karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'unit_kerja_asal.integer' => 'Data asal unit kerja karyawan sebelumnya adalah berupa satuan angka.',
            'unit_kerja_asal.required' => 'Data asal unit kerja karyawan otomatis akan terisi jika karyawan terpilih.',
            'unit_kerja_tujuan.required' => 'Silahkan pilih tujuan unit kerja karyawan yang tersedia terlebih dahulu.',
            'unit_kerja_tujuan.integer' => 'Data tujuan unit kerja karyawan yang valid adalah berupa satuan angka.',
            'jabatan_asal.integer' => 'Data jabatan karyawan sebelumnya adalah berupa satuan angka.',
            'jabatan_asal.required' => 'Data asal jabatan karyawan otomatis akan terisi jika karyawan terpilih.',
            'jabatan_tujuan.required' => 'Silahkan pilih tujuan jabatan karyawan yang tersedia terlebih dahulu.',
            'jabatan_tujuan.integer' => 'Data tujuan jabatan karyawan yang valid adalah berupa satuan angka.',
            'alasan.required' => 'Alasan transfer karyawan tidak diperbolehkan kosong.',
            'alasan.string' => 'Alasan transfer karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'dokumen.required' => 'Dokumen transfer karyawan tidak diperbolehkan kosong.',
            'dokumen.file' => 'Dokumen yang diperbolehkan berupa berkas file .PDF, .XLS, .XLSX, .DOC, dan .DOCX',
            'dokumen.mimes' => 'Dokumen yang diperbolehkan berupa berkas file .PDF, .XLS, .XLSX, .DOC, dan .DOCX',
            'dokumen.max' => 'Dokumen yang diunggah harus kurang dari 2 MB.',
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

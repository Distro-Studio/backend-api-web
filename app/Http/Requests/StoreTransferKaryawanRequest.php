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
            'tanggal' => 'required|date',
            'tipe' => 'required|string',
            'unit_kerja_to' => 'required|integer',
            'unit_kerja_from' => 'nullable|integer',
            'jabatan_to' => 'required|integer',
            'jabatan_from' => 'nullable|integer',
            'alasan' => 'required|string',
            'dokumen' => 'nullable|file|max:5048',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            'tanggal.required' => 'Silahkan masukkan tanggal terlebih dahulu.',
            'tanggal.date' => 'Data tanggal yang valid adalah berupa tanggal dan waktu.',
            'tipe.required' => 'Batas penghasilan awal tidak diperbolehkan kosong.',
            'tipe.string' => 'Batas penghasilan awal tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'unit_kerja_from.integer' => 'Data unit kerja karyawan sebelumnya adalah berupa satuan angka.',
            'unit_kerja_to.required' => 'Silahkan pilih unit kerja yang tersedia terlebih dahulu.',
            'unit_kerja_to.integer' => 'Data unit kerja yang valid adalah berupa satuan angka.',
            'jabatan_from.integer' => 'Data jabatan karyawan sebelumnya adalah berupa satuan angka.',
            'jabatan_to.required' => 'Silahkan pilih jabatan yang tersedia terlebih dahulu.',
            'jabatan_to.integer' => 'Data jabatan yang valid adalah berupa satuan angka.',
            'alasan.required' => 'Alasan transfer karyawan tidak diperbolehkan kosong.',
            'alasan.string' => 'Alasan transfer karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'dokumen.file' => 'Dokumen yang diperbolehkan berupa berkas file seperti .PDF, .XLS, .XLSX, dan lainnya.',
            'dokumen.max' => 'Dokumen yang diunggah harus kurang dari 5 MB.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLemburKaryawanRequest extends FormRequest
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
            'shift_id' => 'required|integer|exists:shifts,id',
            'tgl_pengajuan' => 'required|date',
            'kompensasi' => 'required|string',
            'tipe' => 'required|string',
            'durasi_jam' => 'required|numeric',
            'durasi_menit' => 'required|numeric',
            'catatan' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            'user_id.exists' => 'Data pengguna yang terdipilih tidak tersedia.',
            'shift_id.required' => 'Silahkan pilih shift yang tersedia terlebih dahulu.',
            'shift_id.integer' => 'Data shift yang valid adalah berupa satuan angka.',
            'shift_id.exists' => 'Data shift yang terdipilih tidak tersedia.',
            'tgl_pengajuan.required' => 'Silahkan pilih tanggal pengajuan terlebih dahulu.',
            'tgl_pengajuan.date' => 'Format tanggal pengajuan harus berupa tanggal.',
            'tipe.required' => 'Silahkan pilih tipe lembur yang tersedia terlebih dahulu.',
            'tipe.string' => 'Tipe lembur karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'kompensasi.required' => 'Kompensasi lembur karyawan tidak diperbolehkan kosong.',
            'kompensasi.string' => 'Kompensasi lembur karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'durasi_jam.required' => 'Durasi lembur karyawan tidak diperbolehkan kosong.',
            'durasi_jam.numeric' => 'Durasi lembur karyawan harus berupa angka.',
            'durasi_menit.required' => 'Durasi lembur karyawan tidak diperbolehkan kosong.',
            'durasi_menit.numeric' => 'Durasi lembur karyawan harus berupa angka.',
            'catatan.required' => 'Catatan lembur karyawan tidak diperbolehkan kosong.',
            'catatan.string' => 'Catatan lembur karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLemburKaryawanRequest extends FormRequest
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
            // 'user_id' => 'required|integer|exists:users,id',
            'jadwal_id' => 'required|integer|exists:jadwals,id',
            'tgl_pengajuan' => 'required|string',
            // 'kompensasi_lembur_id' => 'required|integer|exists:kategori_kompensasis,id',
            'durasi' => 'required|string',
            'catatan' => 'required|string',
            // 'status_lembur_id' => 'required|integer|exists:status_lemburs,id',
        ];
    }

    public function messages(): array
    {
        return [
            // 'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            // 'user_id.integer' => 'Data pengguna yang valid adalah berupa satuan angka.',
            // 'user_id.exists' => 'Data pengguna yang terdipilih tidak tersedia.',
            'jadwal_id.required' => 'Silahkan pilih jadwal yang tersedia terlebih dahulu.',
            'jadwal_id.integer' => 'Data jadwal yang valid adalah berupa satuan angka.',
            'jadwal_id.exists' => 'Data jadwal yang terdipilih tidak tersedia.',
            'tgl_pengajuan.required' => 'Silahkan pilih tanggal pengajuan terlebih dahulu.',
            'tgl_pengajuan.string' => 'Tanggal pengajuan yang valid adalah berupa satuan angka dan huruf.',
            // 'kompensasi_lembur_id.required' => 'Silahkan pilih kompensasi lembur yang tersedia terlebih dahulu.',
            // 'kompensasi_lembur_id.integer' => 'Data kompensasi lembur yang valid adalah berupa satuan angka.',
            // 'kompensasi_lembur_id.exists' => 'Data kompensasi lembur yang terdipilih tidak tersedia.',
            'durasi.required' => 'Durasi lembur karyawan tidak diperbolehkan kosong.',
            'durasi.string' => 'Durasi lembur karyawan harus berupa angka dan huruf.',
            'catatan.required' => 'Catatan lembur karyawan tidak diperbolehkan kosong.',
            'catatan.string' => 'Catatan lembur karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
            // 'status_lembur_id.required' => 'Silahkan pilih status lembur yang tersedia terlebih dahulu.',
            // 'status_lembur_id.integer' => 'Data status lembur yang valid adalah berupa satuan angka.',
            // 'status_lembur_id.exists' => 'Data status lembur yang terdipilih tidak tersedia.',
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

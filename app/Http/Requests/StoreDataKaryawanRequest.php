<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDataKaryawanRequest extends FormRequest
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
            'nama' => 'required|string|max:225',
            'email' => 'required|email|max:225|unique:data_karyawans,email',
            'role_id' => 'required|integer|exists:roles,id',
            'no_rm' => 'required|string',
            'no_manulife' => 'nullable|string',
            'tgl_masuk' => 'required|date',
            'unit_kerja_id' => 'required|integer|exists:unit_kerjas,id',
            'jabatan_id' => 'required|integer|exists:jabatans,id',
            'kompetensi_id' => 'required|integer|exists:kompetensis,id',
            'status_karyawan' => 'required|string',

            // Step 2
            'kelompok_gaji_id' => 'required|integer|exists:kelompok_gajis,id',
            'no_rekening' => 'required|numeric',
            'tunjangan_jabatan' => 'required|numeric',
            'tunjangan_fungsional' => 'required|numeric',
            'tunjangan_khusus' => 'required|numeric',
            'tunjangan_lainnya' => 'required|numeric',
            'uang_makan' => 'required|numeric',
            'uang_lembur' => 'nullable|numeric',
            'ptkp_id' => 'required|integer|exists:ptkps,id',

            // Step 3
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'email.required' => 'Email karyawan tidak diperbolehkan kosong.',
            'email.email' => 'Alamat email yang valid wajib menggunakan @.',
            'email.max' => 'Email karyawan melebihi batas maksimum panjang karakter.',
            'email.unique' => 'Email karyawan tersebut sudah pernah digunakan.',
            'role_id.required' => 'Silahkan pilih role untuk karyawan terlebih dahulu.',
            'role_id.exists' => 'Maaf role tersebut tidak valid.',
            'no_rm.required' => 'Nomor rekam medis karyawan tidak diperbolehkan kosong.',
            'no_manulife.string' => 'Nomor manulife karyawan tidak diperbolehkan kosong.',
            'tgl_masuk.required' => 'Tanggal masuk karyawan tidak diperbolehkan kosong.',
            'tgl_masuk.date' => 'Tanggal masuk karyawan harus berupa tanggal.',
            'unit_kerja_id.required' => 'Silahkan pilih unit kerja karyawan terlebih dahulu.',
            'unit_kerja_id.exists' => 'Maaf unit kerja tersebut tidak valid.',
            'jabatan_id.required' => 'Silahkan pilih jabatan karyawan terlebih dahulu.',
            'jabatan_id.exists' => 'Maaf jabatan tersebut tidak valid.',
            'kompetensi_id.required' => 'Silahkan pilih kompetensi karyawan terlebih dahulu.',
            'kompetensi_id.exists' => 'Maaf kompetensi tersebut tidak valid.',
            'status_karyawan.required' => 'Status karyawan tidak diperbolehkan kosong.',
            'status_karyawan.string' => 'Status karyawan tidak diperbolehkan mengandung angka.',

            'kelompok_gaji_id.required' => 'Silahkan pilih kelompok gaji karyawan terlebih dahulu.',
            'kelompok_gaji_id.exists' => 'Maaf kelompok gaji tersebut tidak valid.',
            'no_rekening.required' => 'Nomor rekening karyawan tidak diperbolehkan kosong.',
            'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung huruf.',
            'no_rekening.max' => 'Nomor rekening karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_jabatan.required' => 'Tunjangan jabatan karyawan tidak diperbolehkan kosong.',
            'tunjangan_jabatan.numeric' => 'Tunjangan jabatan karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_jabatan.max' => 'Tunjangan jabatan karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_fungsional.required' => 'Tunjangan fungsional karyawan tidak diperbolehkan kosong.',
            'tunjangan_fungsional.numeric' => 'Tunjangan fungsional karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_fungsional.max' => 'Tunjangan fungsional karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_khusus.required' => 'Tunjangan khusus karyawan tidak diperbolehkan kosong.',
            'tunjangan_khusus.numeric' => 'Tunjangan khusus karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_khusus.max' => 'Tunjangan khusus karyawan melebihi batas maksimum panjang karakter.',
            'tunjangan_lainnya.required' => 'Tunjangan karyawan lainya tidak diperbolehkan kosong.',
            'tunjangan_lainnya.numeric' => 'Tunjangan karyawan lainya tidak diperbolehkan mengandung huruf.',
            'tunjangan_lainnya.max' => 'Tunjangan lainya karyawan melebihi batas maksimum panjang karakter.',
            'uang_makan.required' => 'Uang makan karyawan tidak diperbolehkan kosong.',
            'uang_makan.numeric' => 'Uang makan karyawan tidak diperbolehkan mengandung huruf.',
            'uang_makan.max' => 'Uang makan karyawan melebihi batas maksimum panjang karakter.',
            'uang_lembur.required' => 'Uang lembur karyawan tidak diperbolehkan kosong.',
            'uang_lembur.numeric' => 'Uang lembur karyawan tidak diperbolehkan mengandung huruf.',
            'uang_lembur.max' => 'Uang lembur karyawan melebihi batas maksimum panjang karakter.',
            'ptkp_id.required' => 'Silahkan pilih PTKP karyawan terlebih dahulu.',
            'ptkp_id.exists' => 'Maaf PTKP tersebut tidak valid.',

            'username.required' => 'Username karyawan tidak diperbolehkan kosong.',
            'username.unique' => 'Username tersebut sudah pernah digunakan.',
            'password.required' => 'Password karyawan tidak diperbolehkan kosong.',
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

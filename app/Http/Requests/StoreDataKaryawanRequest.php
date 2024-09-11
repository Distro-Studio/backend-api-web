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
            'email' => 'nullable|email|string|unique:data_karyawans,email',
            'role_id' => 'required|integer|exists:roles,id',
            'no_rm' => 'required|string',
            'no_manulife' => 'nullable|string',
            'tgl_masuk' => 'required|string',
            'tgl_berakhir_pks' => 'required|string',
            'nik' => 'required|numeric',
            'unit_kerja_id' => 'required|integer|exists:unit_kerjas,id',
            'jabatan_id' => 'required|integer|exists:jabatans,id',
            'kompetensi_id' => 'nullable|integer|exists:kompetensis,id',
            'status_karyawan_id' => 'required|integer|exists:status_karyawans,id',
            'premi_id' => 'array|nullable',
            'premi_id.*' => 'integer|exists:premis,id',

            // Step 2
            'kelompok_gaji_id' => 'required|integer|exists:kelompok_gajis,id',
            'no_rekening' => 'required|numeric',
            // 'tunjangan_jabatan' => 'required|numeric',
            'tunjangan_fungsional' => 'required|numeric',
            'tunjangan_khusus' => 'required|numeric',
            'tunjangan_lainnya' => 'required|numeric',
            'uang_makan' => 'required|numeric',
            'uang_lembur' => 'nullable|numeric',
            'ptkp_id' => 'required|integer|exists:ptkps,id',

            'tgl_diangkat' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            // 'email.required' => 'Email karyawan tidak diperbolehkan kosong.',
            'email.string' => 'Email karyawan tidak diperbolehkan mengandung selain huruf.',
            'email.email' => 'Alamat email yang valid wajib menggunakan @.',
            'email.max' => 'Email karyawan melebihi batas maksimum panjang karakter.',
            'email.unique' => 'Email karyawan tersebut sudah pernah digunakan.',
            'role_id.required' => 'Silahkan pilih role untuk karyawan terlebih dahulu.',
            'role_id.exists' => 'Maaf role yang dipilih tidak valid.',
            'no_rm.required' => 'Nomor rekam medis karyawan tidak diperbolehkan kosong.',
            'no_manulife.string' => 'Nomor manulife karyawan tidak diperbolehkan kosong.',
            'nik.required' => 'Nomor induk karyawan tidak diperbolehkan kosong.',
            'nik.string' => 'Nomor induk karyawan tidak diperbolehkan kosong.',
            'nik.numeric' => 'Nomor induk karyawan tidak diperbolehkan mengandung selain angka.',
            'tgl_masuk.required' => 'Tanggal masuk karyawan tidak diperbolehkan kosong.',
            'tgl_masuk.string' => 'Tanggal masuk karyawan tidak diperbolehkan mengandung selain angka dan huruf.',
            'tgl_berakhir_pks.required' => 'Tanggal berakhir PKS karyawan tidak diperbolehkan kosong.',
            'tgl_berakhir_pks.string' => 'Tanggal berakhir PKS karyawan tidak diperbolehkan mengandung selain angka dan huruf.',
            'unit_kerja_id.required' => 'Silahkan pilih unit kerja karyawan terlebih dahulu.',
            'unit_kerja_id.exists' => 'Maaf unit kerja yang dipilih tidak valid.',
            'jabatan_id.required' => 'Silahkan pilih jabatan karyawan terlebih dahulu.',
            'jabatan_id.exists' => 'Maaf jabatan yang dipilih tidak valid.',
            // 'kompetensi_id.required' => 'Silahkan pilih kompetensi karyawan terlebih dahulu.',
            'kompetensi_id.exists' => 'Maaf kompetensi yang dipilih tidak valid.',
            'status_karyawan_id.required' => 'Silahkan pilih status karyawan terlebih dahulu.',
            'status_karyawan_id.exists' => 'Maaf status karyawan yang dipilih tidak valid.',
            // 'premi_id.required' => 'Silahkan pilih potongan penggajian karyawan terlebih dahulu.',
            'premi_id.array' => 'Potongan penggajian harus berupa array.',
            'premi_id.*.exists' => 'Maaf potongan penggajian yang dipilih tidak valid.',

            'kelompok_gaji_id.required' => 'Silahkan pilih kelompok gaji karyawan terlebih dahulu.',
            'kelompok_gaji_id.exists' => 'Maaf kelompok gaji yang dipilih tidak valid.',
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
            'ptkp_id.exists' => 'Maaf PTKP yang dipilih tidak valid.',

            'tgl_diangkat.required' => 'Tanggal diangkat karyawan tidak diperbolehkan kosong.',
            'tgl_diangkat.string' => 'Tanggal diangkat karyawan tidak diperbolehkan mengandung selain huruf.'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $validator->errors()
        ];

        // $messages = implode(' ', $validator->errors()->all());
        // $response = [
        //     'status' => Response::HTTP_BAD_REQUEST,
        //     'message' => $messages,
        // ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

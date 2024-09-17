<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDataKaryawanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:225',
            'email' => 'nullable|email|string',
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
            'tunjangan_fungsional' => 'required|numeric',
            'tunjangan_khusus' => 'required|numeric',
            'tunjangan_lainnya' => 'required|numeric',
            'uang_makan' => 'required|numeric',
            'uang_lembur' => 'nullable|numeric',
            'ptkp_id' => 'required|integer|exists:ptkps,id',

            // tambahan dari mobile
            'tempat_lahir' => 'nullable',
            'tgl_lahir' => 'nullable|string',
            'no_hp' => 'nullable|numeric',
            'jenis_kelamin' => 'nullable|in:0,1',
            'nik_ktp' => 'nullable|integer|digits:16',
            'no_kk' => 'nullable|integer|digits:16',
            'kategori_agama_id' => 'nullable|exists:kategori_agamas,id',
            'kategori_darah_id' => 'nullable|exists:kategori_darahs,id',
            'tinggi_badan' => 'nullable|integer',
            'berat_badan' => 'nullable|integer',
            'gelar_depan' => 'nullable|string',
            'gelar_belakang' => 'nullable|string',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'tahun_lulus' => 'nullable|numeric',
            'no_ijazah' => 'nullable',

            'no_str' => 'nullable',
            'masa_berlaku_str' => 'nullable|string',
            'no_sip' => 'nullable',
            'masa_berlaku_sip' => 'nullable|string',
            'no_bpjsksh' => 'nullable',
            'no_bpjsktk' => 'nullable',

            'pendidikan_terakhir' => 'nullable|exists:kategori_pendidikans,id',
            'asal_sekolah' => 'nullable|string',
            'tgl_diangkat' => 'nullable|string'
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
            'role_id.required' => 'Silahkan pilih role untuk karyawan terlebih dahulu.',
            'role_id.exists' => 'Maaf role yang dipilih tidak valid.',
            'no_rm.required' => 'Nomor rekam medis karyawan tidak diperbolehkan kosong.',
            'no_manulife.string' => 'Nomor manulife karyawan tidak diperbolehkan kosong.',
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
            'kompetensi_id.exists' => 'Maaf kompetensi yang dipilih tidak valid.',
            'status_karyawan_id.required' => 'Silahkan pilih status karyawan terlebih dahulu.',
            'status_karyawan_id.exists' => 'Maaf status karyawan yang dipilih tidak valid.',
            'premi_id.array' => 'Potongan penggajian harus berupa array.',
            'premi_id.*.exists' => 'Maaf potongan penggajian yang dipilih tidak valid.',

            'kelompok_gaji_id.required' => 'Silahkan pilih kelompok gaji karyawan terlebih dahulu.',
            'kelompok_gaji_id.exists' => 'Maaf kelompok gaji yang dipilih tidak valid.',
            'no_rekening.required' => 'Nomor rekening karyawan tidak diperbolehkan kosong.',
            'no_rekening.numeric' => 'Nomor rekening karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_fungsional.required' => 'Tunjangan fungsional karyawan tidak diperbolehkan kosong.',
            'tunjangan_fungsional.numeric' => 'Tunjangan fungsional karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_khusus.required' => 'Tunjangan khusus karyawan tidak diperbolehkan kosong.',
            'tunjangan_khusus.numeric' => 'Tunjangan khusus karyawan tidak diperbolehkan mengandung huruf.',
            'tunjangan_lainnya.required' => 'Tunjangan karyawan lainya tidak diperbolehkan kosong.',
            'tunjangan_lainnya.numeric' => 'Tunjangan karyawan lainya tidak diperbolehkan mengandung huruf.',
            'uang_makan.required' => 'Uang makan karyawan tidak diperbolehkan kosong.',
            'uang_makan.numeric' => 'Uang makan karyawan tidak diperbolehkan mengandung huruf.',
            'uang_lembur.required' => 'Uang lembur karyawan tidak diperbolehkan kosong.',
            'uang_lembur.numeric' => 'Uang lembur karyawan tidak diperbolehkan mengandung huruf.',
            'ptkp_id.required' => 'Silahkan pilih PTKP karyawan terlebih dahulu.',
            'ptkp_id.exists' => 'Maaf PTKP yang dipilih tidak valid.',

            'tgl_lahir.string' => 'Tanggal lahir karyawan tidak diperbolehkan mengandung selain angka.',
            'no_hp.numeric' => 'Nomor HP karyawan tidak diperbolehkan mengandung selain angka.',
            'jenis_kelamin.in' => 'Jenis kelamin karyawan tidak diperbolehkan selain laki-laki atau perempuan.',
            'nik_ktp.integer' => 'NIK KTP karyawan tidak diperbolehkan mengandung selain angka.',
            'nik_ktp.digits' => 'NIK KTP karyawan melebihi batas maksimum panjang 16 karakter.',
            'no_kk.integer' => 'Nomor KK karyawan tidak diperbolehkan mengandung selain angka.',
            'no_kk.digits' => 'Nomor KK karyawan melebihi batas maksimum panjang 16 karakter.',
            'kategori_agama_id.exists' => 'Agama karyawan yang dipilih yang tidak valid.',
            'kategori_darah_id.exists' => 'Golongan darah karyawan yang dipilih tidak valid.',
            'tinggi_badan.integer' => 'Tinggi badan karyawan tidak diperbolehkan mengandung selain angka.',
            'berat_badan.integer' => 'Berat badan karyawan tidak diperbolehkan mengandung selain angka.',
            'gelar_depan.string' => 'Gelar depan karyawan hanya diperbolehkan mengandung huruf.',
            'gelar_belakang.string' => 'Gelar belakang karyawan hanya diperbolehkan mengandung huruf.',
            'tahun_lulus.numeric' => 'Tahun lulus karyawan tidak diperbolehkan mengandung selain angka.',
            
            'masa_berlaku_str.string' => 'Masa berlaku STR karyawan tidak diperbolehkan mengandung selain angka.',
            'masa_berlaku_sip.string' => 'Masa berlaku SIP karyawan hanya diperbolehkan mengandung angka dan huruf.',
            
            'pendidikan_terakhir.exists' => 'Pendidikan terakhir karyawan tersebut tidak valid.',
            'asal_sekolah.string' => 'Asal sekolah karyawan tidak diperbolehkan mengandung selain huruf.',
            'tgl_diangkat.string' => 'Tanggal diangkat karyawan tidak diperbolehkan mengandung selain huruf.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $reponse = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($reponse, Response::HTTP_BAD_REQUEST));
        // $messages = implode(' ', $validator->errors()->all());
        // $response = [
        //     'status' => Response::HTTP_BAD_REQUEST,
        //     'message' => $messages,
        // ];

        // throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

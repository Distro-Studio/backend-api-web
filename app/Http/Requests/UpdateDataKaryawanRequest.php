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
            'email' => 'required|email|string',
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
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|string',
            'no_hp' => 'required|numeric',
            'jenis_kelamin' => 'required|in:0,1',
            'nik_ktp' => 'required|integer|digits:16',
            'no_kk' => 'required|integer|digits:16',
            'kategori_agama_id' => 'required|exists:kategori_agamas,id',
            'kategori_darah_id' => 'required|exists:kategori_darahs,id',
            'tinggi_badan' => 'required|integer',
            'berat_badan' => 'required|integer',
            'gelar_depan' => 'nullable|string',
            'npwp' => 'required',
            'alamat' => 'required',
            'tahun_lulus' => 'required|numeric',
            'no_ijazah' => 'required',

            'no_str' => 'required',
            'masa_berlaku_str' => 'nullable|string',
            'no_sip' => 'required',
            'masa_berlaku_sip' => 'nullable|string',
            'no_bpjsksh' => 'required',
            'no_bpjsktk' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'email.required' => 'Email karyawan tidak diperbolehkan kosong.',
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

            'tempat_lahir.required' => 'Tempat lahir karyawan tidak diperbolehkan kosong.',
            'tanggal_lahir.required' => 'Tanggal lahir karyawan tidak diperbolehkan kosong.',
            'tanggal_lahir.string' => 'Tanggal lahir karyawan tidak diperbolehkan mengandung selain angka.',
            'no_hp.required' => 'Nomor HP karyawan tidak diperbolehkan kosong.',
            'no_hp.numeric' => 'Nomor HP karyawan tidak diperbolehkan mengandung selain angka.',
            'jenis_kelamin.required' => 'Jenis kelamin karyawan tidak diperbolehkan kosong.',
            'jenis_kelamin.in' => 'Jenis kelamin karyawan tidak diperbolehkan selain laki-laki atau perempuan.',
            'nik_ktp.required' => 'NIK KTP karyawan tidak diperbolehkan kosong.',
            'nik_ktp.integer' => 'NIK KTP karyawan tidak diperbolehkan mengandung selain angka.',
            'nik_ktp.digits' => 'NIK KTP karyawan melebihi batas maksimum panjang 16 karakter.',
            'no_kk.required' => 'Nomor KK karyawan tidak diperbolehkan kosong.',
            'no_kk.integer' => 'Nomor KK karyawan tidak diperbolehkan mengandung selain angka.',
            'no_kk.digits' => 'Nomor KK karyawan melebihi batas maksimum panjang 16 karakter.',
            'kategori_agama_id.required' => 'Agama karyawan tidak diperbolehkan kosong.',
            'kategori_agama_id.exists' => 'Agama karyawan yang dipilih yang tidak valid.',
            'kategori_darah_id.required' => 'Silahkan pilih Golongan darah karyawan terlebih dahulu.',
            'kategori_darah_id.exists' => 'Golongan darah karyawan yang dipilih tidak valid.',
            'tinggi_badan.required' => 'Tinggi badan karyawan tidak diperbolehkan kosong.',
            'tinggi_badan.integer' => 'Tinggi badan karyawan tidak diperbolehkan mengandung selain angka.',
            'berat_badan.required' => 'Berat badan karyawan tidak diperbolehkan kosong.',
            'berat_badan.integer' => 'Berat badan karyawan tidak diperbolehkan mengandung selain angka.',
            'gelar_depan.string' => 'Gelar depan karyawan hanya diperbolehkan mengandung huruf.',
            'npwp.required' => 'NPWP karyawan tidak diperbolehkan kosong.',
            'alamat.required' => 'Alamat karyawan tidak diperbolehkan kosong.',
            'tahun_lulus.required' => 'Tahun lulus karyawan tidak diperbolehkan kosong.',
            'tahun_lulus.numeric' => 'Tahun lulus karyawan tidak diperbolehkan mengandung selain angka.',
            'no_ijazah.required' => 'Nomor ijazah karyawan tidak diperbolehkan kosong.',

            'no_str.required' => 'Nomor STR karyawan tidak diperbolehkan kosong.',
            'masa_berlaku_str.string' => 'Masa berlaku STR karyawan tidak diperbolehkan mengandung selain angka.',
            'no_sip.required' => 'Nomor SIP karyawan tidak diperbolehkan kosong.',
            'masa_berlaku_sip.string' => 'Masa berlaku SIP karyawan hanya diperbolehkan mengandung angka dan huruf.',
            'no_bpjsksh.required' => 'Nomor BPJS Kesehatan karyawan tidak diperbolehkan kosong.',
            'no_bpjsktk.required' => 'Nomor BPJS Ketenagakerjaan karyawan tidak diperbolehkan kosong.',

            // file
            // 'file_ktp.required' => 'File KTP karyawan tidak diperbolehkan kosong.',
            // 'file_ktp.file' => 'File KTP karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_ktp.max' => 'File KTP karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_kk.required' => 'File KK karyawan tidak diperbolehkan kosong.',
            // 'file_kk.file' => 'File KK karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_kk.max' => 'File KK karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_sip.required' => 'File SIP karyawan tidak diperbolehkan kosong.',
            // 'file_sip.file' => 'File SIP karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_sip.max' => 'File SIP karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_bpjs_kesehatan.required' => 'File BPJS Kesehatan karyawan tidak diperbolehkan kosong.',
            // 'file_bpjs_kesehatan.file' => 'File BPJS Kesehatan karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_bpjs_kesehatan.max' => 'File BPJS Kesehatan karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_bpjs_ketenagakerjaan.required' => 'File BPJS Ketenagakerjaan karyawan tidak diperbolehkan kosong.',
            // 'file_bpjs_ketenagakerjaan.file' => 'File BPJS Ketenagakerjaan karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_bpjs_ketenagakerjaan.max' => 'File BPJS Ketenagakerjaan karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_ijazah.required' => 'File Ijazah karyawan tidak diperbolehkan kosong.',
            // 'file_ijazah.file' => 'File Ijazah karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_ijazah.max' => 'File Ijazah karyawan yang diunggah harus kurang dari 10 MB.',
            // 'file_sertifikat.required' => 'File Sertifikat karyawan tidak diperbolehkan kosong.',
            // 'file_sertifikat.file' => 'File Sertifikat karyawan tidak diperbolehkan mengandung selain dokumen.',
            // 'file_sertifikat.max' => 'File Sertifikat karyawan yang diunggah harus kurang dari 10 MB.',
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

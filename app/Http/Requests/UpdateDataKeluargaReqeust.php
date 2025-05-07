<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDataKeluargaReqeust extends FormRequest
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
            'nama_keluarga' => 'required|string|max:255',
            'hubungan' => 'required',
            'pendidikan_terakhir' => 'nullable|integer|exists:kategori_pendidikans,id',
            'tgl_lahir' => 'required',
            'tempat_lahir' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|boolean',
            'kategori_agama_id' => 'nullable|integer|exists:kategori_agamas,id',
            'kategori_darah_id' => 'nullable|integer|exists:kategori_darahs,id',
            'no_rm' => 'nullable',
            'status_hidup' => 'required|boolean',
            'pekerjaan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|numeric',
            'email' => 'nullable|email|max:255',
            'is_bpjs' => 'required|boolean',
            'is_menikah' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'nama_keluarga.required' => 'Nama anggota keluarga harus diisi.',
            'nama_keluarga.string' => 'Nama anggota keluarga harus berupa text.',
            'nama_keluarga.max' => 'Nama anggota keluarga maksimal 255 karakter.',
            'hubungan.required' => 'Hubungan keluarga harus diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir harus diisi.',
            'pendidikan_terakhir.integer' => 'Pendidikan terakhir harus berupa angka.',
            'pendidikan_terakhir.exists' => 'Pendidikan terakhir tidak ditemukan dalam daftar yang valid.',
            'tgl_lahir.required' => 'Tanggal lahir anggota keluarga harus diisi.',
            'tempat_lahir.required' => 'Tempat lahir anggota keluarga harus diisi.',
            'tempat_lahir.string' => 'Tempat lahir harus berupa text.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi.',
            'jenis_kelamin.boolean' => 'Jenis kelamin harus berupa true atau false.',
            'kategori_agama_id.required' => 'Kategori agama harus diisi.',
            'kategori_agama_id.integer' => 'Kategori agama harus berupa angka.',
            'kategori_agama_id.exists' => 'Kategori agama tidak ditemukan dalam daftar yang valid.',
            'kategori_darah_id.required' => 'Kategori darah harus diisi.',
            'kategori_darah_id.integer' => 'Kategori darah harus berupa angka.',
            'kategori_darah_id.exists' => 'Kategori darah tidak ditemukan dalam daftar yang valid.',
            'status_hidup.required' => 'Status hidup harus diisi.',
            'status_hidup.boolean' => 'Status hidup harus berupa true atau false.',
            'pekerjaan.string' => 'Pekerjaan harus berupa string.',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter.',
            'no_hp.numeric' => 'Nomor HP harus berupa angka.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'is_bpjs.required' => 'Status BPJS harus diisi.',
            'is_bpjs.boolean' => 'Status BPJS harus berupa true atau false.',
            'is_menikah.boolean' => 'Status menikah harus berupa true atau false.',
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

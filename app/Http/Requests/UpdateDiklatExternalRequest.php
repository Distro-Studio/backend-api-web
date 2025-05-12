<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDiklatExternalRequest extends FormRequest
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
            'dokumen' => 'required|mimes:jpeg,png,jpg,pdf|max:10240',
            'nama' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'deskripsi' => 'required|string|max:225',
            'tgl_mulai' => 'required|string',
            'tgl_selesai' => 'required|string',
            'jam_mulai' => 'required|string',
            'jam_selesai' => 'required|string',
            'lokasi' => 'required|string',
            'skp' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'dokumen.mimes' => 'Gambar yang diperbolehkan berformat jpeg, png, jpg, atau pdf.',
            'dokumen.max' => 'Ukuran gambar maksimal adalah 10MB.',
            'dokumen.required' => 'Dokumen diklat tidak diperbolehkan kosong.',
            'nama.required' => 'Nama diklat tidak diperbolehkan kosong.',
            'nama.string' => 'Nama diklat hanya diperbolehkan menggunakan angka dan huruf.',
            'user_id.required' => 'Silahkan pilih karyawan diklat terlebih dahulu.',
            'user_id.integer' => 'Karyawan diklat yang disimpan harus berupa angka.',
            'user_id.exists' => 'Karyawan diklat yang dipilih tidak valid.',
            'deskripsi.required' => 'Deskripsi diklat tidak diperbolehkan kosong.',
            'tgl_mulai.required' => 'Tanggal mulai diklat tidak diperbolehkan kosong.',
            'tgl_mulai.string' => 'Tanggal mulai diklat harus berupa angka dan teks.',
            'tgl_selesai.required' => 'Tanggal selesai diklat tidak diperbolehkan kosong.',
            'tgl_selesai.string' => 'Tanggal selesai diklat harus sama atau setelah tanggal mulai.',
            'jam_mulai.required' => 'Jam mulai diklat tidak diperbolehkan kosong.',
            'jam_mulai.string' => 'Jam mulai diklat harus berupa angka dan teks.',
            'jam_selesai.required' => 'Jam selesai diklat tidak diperbolehkan kosong.',
            'jam_selesai.string' => 'Jam selesai diklat harus berupa angka dan teks.',
            'lokasi.required' => 'Lokasi diklat tidak diperbolehkan kosong.',
            'skp.string' => 'Skp diklat hanya diperbolehkan menggunakan angka dan huruf.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDiklatRequest extends FormRequest
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
            'dokumen' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:10240',
            'nama' => 'required|string|max:255',
            // 'kategori_diklat_id' => 'required|integer|exists:kategori_diklats,id',
            'deskripsi' => 'required|string|max:225',
            'user_id' => 'nullable|array', // Penerima notifikasi
            'user_id.*' => 'integer|exists:users,id',
            'dokumen_diklat' => 'nullable|array|max:5',
            'dokumen_diklat.*' => 'file|max:10240|mimes:pdf,ppt,pptx,doc,docx',
            'kuota' => 'required|integer|min:1',
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
            'dokumen.image' => 'File gambar yang diperbolehkan berupa gambar.',
            'dokumen.mimes' => 'Gambar yang diperbolehkan berformat jpeg, png, jpg, atau svg.',
            'dokumen.max' => 'Ukuran gambar maksimal adalah 10MB.',
            'nama.required' => 'Nama diklat tidak diperbolehkan kosong.',
            'nama.string' => 'Nama diklat hanya diperbolehkan menggunakan angka dan huruf.',
            'kategori_diklat_id.required' => 'Silahkan pilih kategori diklat terlebih dahulu.',
            'kategori_diklat_id.exists' => 'Kategori diklat yang dipilih tidak valid.',
            'deskripsi.required' => 'Deskripsi diklat tidak diperbolehkan kosong.',
            'user_id.array' => 'Format data user_id harus berupa array.',
            'user_id.*.integer' => 'Setiap user_id harus berupa angka.',
            'user_id.*.exists' => 'Salah satu user_id tidak ditemukan dalam sistem.',
            'dokumen_diklat.array' => 'Dokumen diklat harus dalam format array.',
            'dokumen_diklat.max' => 'Maksimal hanya dapat mengunggah 5 dokumen diklat.',
            'dokumen_diklat.*.file' => 'Setiap dokumen harus berupa file.',
            'dokumen_diklat.*.max' => 'Setiap file dokumen tidak boleh lebih dari 10MB.',
            'dokumen_diklat.*.mimes' => 'Format dokumen hanya diperbolehkan: pdf, ppt, pptx, doc docx.',
            'kuota.required' => 'Kuota peserta diklat tidak diperbolehkan kosong.',
            'kuota.integer' => 'Kuota peserta diklat harus berupa angka.',
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

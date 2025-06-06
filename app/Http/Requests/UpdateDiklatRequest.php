<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDiklatRequest extends FormRequest
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
            'dokumen' => 'nullable|image|max:10240|mimes:jpeg,png,jpg,svg',
            'nama' => 'required|string|max:255',
            // 'kategori_diklat_id' => 'required|integer|exists:kategori_diklats,id',
            'deskripsi' => 'required|string|max:225',
            // 'user_id' => 'nullable|array', // Penerima notifikasi
            // 'user_id.*' => 'integer|exists:users,id',
            'dokumen_diklat_1' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_diklat_2' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_diklat_3' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_diklat_4' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_diklat_5' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'kuota' => 'nullable|integer|min:1',
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

            'dokumen_diklat_1.file' => 'Dokumen diklat 1 harus berupa file.',
            'dokumen_diklat_1.max' => 'Dokumen diklat 1 tidak boleh lebih dari 10 MB.',
            'dokumen_diklat_1.mimes' => 'Dokumen diklat 1 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_diklat_2.file' => 'Dokumen diklat 2 harus berupa file.',
            'dokumen_diklat_2.max' => 'Dokumen diklat 2 tidak boleh lebih dari 10 MB.',
            'dokumen_diklat_2.mimes' => 'Dokumen diklat 2 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_diklat_3.file' => 'Dokumen diklat 3 harus berupa file.',
            'dokumen_diklat_3.max' => 'Dokumen diklat 3 tidak boleh lebih dari 10 MB.',
            'dokumen_diklat_3.mimes' => 'Dokumen diklat 3 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_diklat_4.file' => 'Dokumen diklat 4 harus berupa file.',
            'dokumen_diklat_4.max' => 'Dokumen diklat 4 tidak boleh lebih dari 10 MB.',
            'dokumen_diklat_4.mimes' => 'Dokumen diklat 4 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_diklat_5.file' => 'Dokumen diklat 5 harus berupa file.',
            'dokumen_diklat_5.max' => 'Dokumen diklat 5 tidak boleh lebih dari 10 MB.',
            'dokumen_diklat_5.mimes' => 'Dokumen diklat 5 harus berupa file PDF, PPTX, atau DOCX.',

            'kuota.required' => 'Kuota peserta diklat tidak diperbolehkan kosong.',
            'kuota.integer' => 'Kuota peserta diklat harus berupa angka.',
            'kuota.min' => 'Kuota peserta diklat tidak boleh kurang dari satu.',
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

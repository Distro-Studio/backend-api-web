<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMateriPelatihanRequest extends FormRequest
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
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'dokumen_materi_1' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_materi_2' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
            'dokumen_materi_3' => 'nullable|file|max:10240|mimes:pdf,pptx,docx',
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul materi pelatihan tidak diperbolehkan kosong.',
            'judul.string' => 'Judul materi pelatihan tidak diperbolehkan mengandung selain angka dan huruf.',
            'judul.max' => 'Judul materi pelatihan tidak diperbolehkan mengandung selain angka dan huruf.',
            'deskripsi.required' => 'Deskripsi materi pelatihan tidak diperbolehkan kosong.',
            'deskripsi.string' => 'Deskripsi materi pelatihan tidak diperbolehkan mengandung selain angka dan huruf.',
            'user_id.required' => 'Verifikator verifikasi tidak diperbolehkan kosong.',
            'user_id.integer' => 'Verifikator verifikasi tidak diperbolehkan mengandung angka.',
            'user_id.exists' => 'Verifikator verifikasi tidak ditemukan.',
            'dokumen_materi_1.file' => 'Dokumen materi harus berupa file.',
            'dokumen_materi_1.max' => 'Dokumen materi 1 tidak boleh lebih dari 10 MB.',
            'dokumen_materi_1.mimes' => 'Dokumen materi 1 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_materi_2.file' => 'Dokumen materi harus berupa file.',
            'dokumen_materi_2.max' => 'Dokumen materi 2 tidak boleh lebih dari 10 MB.',
            'dokumen_materi_2.mimes' => 'Dokumen materi 2 harus berupa file PDF, PPTX, atau DOCX.',
            'dokumen_materi_3.file' => 'Dokumen materi harus berupa file.',
            'dokumen_materi_3.max' => 'Dokumen materi 3 tidak boleh lebih dari 10 MB.',
            'dokumen_materi_3.mimes' => 'Dokumen materi 3 harus berupa file PDF, PPTX, atau DOCX.',
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

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
            'dokumen.*' => 'nullable|file|max:10240|mimes:pdf, pptx, docx',
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
            'dokumen.file' => 'Dokumen materi harus berupa file.',
            'dokumen.max' => 'Dokumen materi maksimal 10 MB.',
            'dokumen.mimes' => 'Dokumen materi harus berupa PDF, PPTX, DOCX.',
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

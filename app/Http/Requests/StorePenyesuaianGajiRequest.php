<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenyesuaianGajiRequest extends FormRequest
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
            'penggajian_id' => 'required|array',
            'penggajian_id.*' => 'exists:penggajians,id',
            'kategori_gaji' => 'required|in:0,1',
            'nama_detail' => 'required|string',
            'besaran' => 'required|numeric',
            'bulan_mulai' => 'nullable|string',
            'bulan_selesai' => 'nullable|string|after_or_equal:bulan_mulai',
        ];
    }

    public function messages()
    {
        return [
            'penggajian_id.required' => 'Silahkan pilih karyawan yang ingin diberikan penyesuaian gaji terlebih dahulu.',
            'penggajian_id.array' => 'Penyesuaian gaji yang dipilih harus berupa array.',
            'penggajian_id.*.exists' => 'Penggajian yang dipilih tidak valid.',
            'kategori.required' => 'Kategori tidak diperbolehkan kosong.',
            'kategori.in' => "Isi kategori harus berupa 1 atau 0.",
            'nama_detail.required' => 'Nama pengurang gaji tidak diperbolehkan kosong.',
            'nama_detail.string' => 'Nama pengurang gaji tidak diperbolehkan mengandung angka.',
            'besaran.required' => 'Besaran pengurang gaji tidak diperbolehkan kosong.',
            'besaran.numeric' => 'Besaran pengurang gaji tidak diperbolehkan mengandung huruf.',
            'bulan_mulai.string' => 'Data tanggal mulai hanya diperbolehkan mengandung huruf dan angka.',
            'bulan_selesai.string' => 'Data tanggal selesai hanya diperbolehkan mengandung huruf dan angka.',
            'bulan_selesai.after_or_equal' => 'Bulan selesai harus setelah atau sama dengan bulan mulai.'
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

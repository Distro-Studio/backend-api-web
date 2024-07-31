<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKompetensiRequest extends FormRequest
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
            'nama_kompetensi' => 'required|string|max:225',
            'jenis_kompetensi' => 'required|boolean',
            'total_tunjangan' => 'required|numeric',
            'nilai_bor' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'nama_kompetensi.required' => 'Nama kompetensi tidak diperbolehkan kosong.',
            'nama_kompetensi.string' => 'Nama kompetensi tidak diperbolehkan mengandung angka.',
            'nama_kompetensi.max' => 'Nama kompetensi melebihi batas maksimum panjang karakter.',
            'nama_kompetensi.unique' => 'Nama kompetensi tersebut sudah pernah dibuat.',
            'jenis_kompetensi.required' => 'Jenis kompetensi tidak diperbolehkan kosong.',
            'jenis_kompetensi.boolean' => 'Jenis kompetensi hanya dapat diisi Medis atau Non-Medis.',
            'total_tunjangan.required' => 'Jumlah tunjangan tidak diperbolehkan kosong.',
            'total_tunjangan.numeric' => 'Jumlah tunjangan tidak diperbolehkan mengandung huruf.',
            'nilai_bor.required' => 'Jumlah BOR tidak diperbolehkan kosong.',
            'nilai_bor.numeric' => 'Jumlah BOR tidak diperbolehkan mengandung huruf.',
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

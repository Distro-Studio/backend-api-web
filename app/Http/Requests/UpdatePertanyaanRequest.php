<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePertanyaanRequest extends FormRequest
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
            'jenis_penilaian_id' => 'required|integer|exists:jenis_penilaians,id',
            'pertanyaan' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_penilaian_id.required' => 'Silahkan pilih jenis penilaian yang tersedia terlebih dahulu.',
            'jenis_penilaian_id.integer' => 'Data jenis penilaian yang valid adalah berupa satuan angka.',
            'jenis_penilaian_id.exists' => 'Data jenis penilaian yang terdipilih tidak tersedia.',
            'pertanyaan.required' => 'Pertanyaan kuesioner tidak diperbolehkan kosong.',
            'pertanyaan.string' => 'Pertanyaan kuesioner tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'pertanyaan.unique' => 'Pertanyaan kuesioner tersebut sudah pernah dibuat.',
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

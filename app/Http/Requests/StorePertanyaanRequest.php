<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePertanyaanRequest extends FormRequest
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
            'role_id' => 'required|integer|exists:roles,id',
            'penilaian_id' => 'required|integer|exists:penilaians,id',
            'pertanyaan' => 'required|string|unique:pertanyaans,pertanyaan',
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Silahkan pilih role yang tersedia terlebih dahulu.',
            'role_id.integer' => 'Data role yang valid adalah berupa satuan angka.',
            'role_id.exists' => 'Data role yang terdipilih tidak tersedia.',
            'penilaian_id.required' => 'Silahkan pilih penilaian yang tersedia terlebih dahulu.',
            'penilaian_id.integer' => 'Data penilaian yang valid adalah berupa satuan angka.',
            'penilaian_id.exists' => 'Data penilaian yang terdipilih tidak tersedia.',
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

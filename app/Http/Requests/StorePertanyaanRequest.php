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
            'pertanyaan' => 'required|string|unique:pertanyaans,pertanyaan',
            'jabatan_id' => 'required|integer|exists:jabatans,id',
        ];
    }

    public function messages(): array
    {
        return [
            'pertanyaan.required' => 'Pertanyaan kuesioner tidak diperbolehkan kosong.',
            'pertanyaan.string' => 'Pertanyaan kuesioner tidak diperbolehkan mengandung angka atau karakter lainnya.',
            'pertanyaan.unique' => 'Pertanyaan kuesioner tersebut sudah pernah dibuat.',
            'jabatan_id.required' => 'Silahkan pilih jabatan yang tersedia terlebih dahulu.',
            'jabatan_id.integer' => 'Data jabatan yang valid adalah berupa satuan angka.',
            'jabatan_id.exists' => 'Data jabatan yang terdipilih tidak tersedia.',
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

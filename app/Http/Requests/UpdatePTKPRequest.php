<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePTKPRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'kode_ptkp' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ptkps')->ignore($id),
            ],
            'kategori_ter_id' => 'required|integer|exists:kategori_ters,id',
            'nilai' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'kode_ptkp.required' => 'Nama PTKP tidak diperbolehkan kosong.',
            'kode_ptkp.string' => 'Nama PTKP tidak diperbolehkan mengandung angka.',
            'kode_ptkp.max' => 'Nama PTKP melebihi batas maksimum panjang karakter.',
            'kode_ptkp.unique' => 'Kode PTKP tersebut sudah pernah dibuat.',
            'kategori_ter_id.required' => 'Kategori TER tidak diperbolehkan kosong.',
            'kategori_ter_id.integer' => 'Kategori TER tidak diperbolehkan mengandung selain angka.',
            'kategori_ter_id.exists' => 'Kategori TER tidak valid.',
            // 'nilai.required' => 'Nilai PTKP tidak diperbolehkan kosong.',
            'nilai.numeric' => 'Nilai PTKP tidak diperbolehkan mengandung selain angka.',
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

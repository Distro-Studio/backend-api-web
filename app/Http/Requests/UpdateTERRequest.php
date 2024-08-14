<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTERRequest extends FormRequest
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
            'kategori_ter_id' => 'required',
            'ptkp_id' => 'required',
            'from_ter' => 'required|numeric',
            'to_ter' => 'required|numeric',
            'percentage' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'kategori_ter_id.required' => 'Silahkan pilih kategori TER terlebih dahulu.',
            'ptkp_id.required' => 'Silahkan pilih PTKP terlebih dahulu.',
            'from_ter.required' => 'Batas awal penghasilan tidak diperbolehkan kosong.',
            'from_ter.numeric' => 'Batas awal penghasilan tidak diperbolehkan mengandung huruf.',
            'to_ter.required' => 'Batas akhir penghasilan tidak diperbolehkan kosong.',
            'to_ter.numeric' => 'Batas akhir penghasilan tidak diperbolehkan mengandung huruf.',
            'percentage.required' => 'Persentase TER tidak diperbolehkan kosong.',
            'percentage.numeric' => 'Persentase TER tidak diperbolehkan mengandung huruf.',
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

<?php

namespace App\Http\Requests\Excel_Import;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportTER21Request extends FormRequest
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
            'ter_pph_file' => 'required|mimes:xlsx,xls,csv',
        ];
    }

    public function messages()
    {
        return [
            'ter_pph_file.required' => 'Silahkan masukkan file data TER21 terlebih dahulu.',
            'ter_pph_file.mimes' => 'File data TER21 wajib berupa excel dan berekstensi .xlsx, .xls, .csv.',
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

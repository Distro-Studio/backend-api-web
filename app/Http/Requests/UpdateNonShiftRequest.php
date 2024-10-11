<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateNonShiftRequest extends FormRequest
{
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
            'jam_from' => 'nullable|string',
            'jam_to' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'jam_from.string' => 'Nama jam kerja tetap tidak diperbolehkan mengandung selain angka dan huruf.',
            'jam_to.string' => 'Nama jam kerja tetap tidak diperbolehkan mengandung selain angka dan huruf.',
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

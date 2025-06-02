<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
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
            'nama' => 'required|string|max:225',
            'unit_kerja_id' => 'required|integer|exists:unit_kerjas,id',
            'jam_from' => 'required|string',
            'jam_to' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama shift tidak diperbolehkan kosong.',
            'nama.string' => 'Nama shift tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama shift melebihi batas maksimum panjang karakter.',
            'nama.unique' => 'Nama shift tersebut sudah pernah dibuat.',
            'unit_kerja_id.required' => 'Unit kerja tidak diperbolehkan kosong.',
            'unit_kerja_id.integer' => 'Unit kerja tidak diperbolehkan mengandung selain angka.',
            'unit_kerja_id.exists' => 'Unit kerja tersebut tidak valid.',
            'jam_from.required' => 'Jam kerja mulai shift tidak diperbolehkan kosong.',
            'jam_from.string' => 'Nama shift tidak diperbolehkan mengandung selain angka dan huruf.',
            'jam_to.required' => 'Jam kerja selesai shift tidak diperbolehkan kosong.',
            'jam_to.string' => 'Nama shift tidak diperbolehkan mengandung selain angka dan huruf.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $reponse = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages
        ];

        throw new HttpResponseException(response()->json($reponse, Response::HTTP_BAD_REQUEST));
    }
}

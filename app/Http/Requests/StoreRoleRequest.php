<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreRoleRequest extends FormRequest
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
            'name' => 'required|string|max:225|unique:roles,name',
            'deskripsi' => 'string|max:225|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama role tidak diperbolehkan kosong.',
            'name.string' => 'Nama role tidak diperbolehkan mengandung angka.',
            'name.max' => 'Nama role melebihi batas maksimum panjang karakter.',
            'name.unique' => 'Nama role tersebut sudah pernah dibuat.',
            'deskripsi.string' => 'Deskripsi role tidak diperbolehkan mengandung angka.',
            'deskripsi.max' => 'Deskripsi role melebihi batas maksimum panjang karakter.',
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

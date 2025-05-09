<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginDashboardRequest extends FormRequest
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
            'email' => ['required'],
            'password' => ['required', 'min:4']
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Silahkan masukkan email anda terlebih dahulu.',
            // 'email.email' => 'Format email yang diperbolehkan menggunakan @gmail atau yang lainnya.',
            'password.required' => 'Kolom password tidak diperbolehkan kosong.',
            'password.min' => 'Minimum password yang diperbolehkan 4 karakter.',
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

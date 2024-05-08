<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|required_with:password',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|required_with:password'
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'Kata sandi lama tidak diperbolehkan kosong.',
            'password.required' => 'Kata sandi baru tidak diperbolehkan kosong.',
            'password.min' => 'Kata sandi baru terlalu pendek, minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai dengan kata sandi baru anda.',
            'password_confirmation.required' => 'Konfirmasi kata sandi tidak diperbolehkan kosong.',
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

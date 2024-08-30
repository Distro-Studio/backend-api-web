<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'kode_otp' => 'required|numeric',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|required_with:password'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email tidak diperbolehkan kosong.',
            'email.email' => 'Email yang valid hanya diperbolehkan menggunakan format email.',
            'password.required' => 'Password tidak diperbolehkan kosong.',
            'password.min' => 'Minimum password yang diperbolehkan 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai dengan kata sandi baru anda.',
            'password_confirmation.required' => 'Konfirmasi kata sandi tidak diperbolehkan kosong.',
            'kode_otp.required' => 'OTP tidak diperbolehkan kosong.',
            'kode_otp.numeric' => 'OTP yang valid hanya diperbolehkan mengandung angka.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $messages = implode(' ', $validator->errors()->all());
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $messages,
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

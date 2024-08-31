<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:data_karyawans,email',
            'kode_otp' => 'required|numeric|digits:6',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email tidak diperbolehkan kosong.',
            'email.email' => 'Email yang valid hanya diperbolehkan menggunakan format email.',
            'email.exists' => 'Email yang diberikan tidak terdaftar di sistem Personalia.',
            'password.required' => 'Kata sandi tidak diperbolehkan kosong.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi kata sandi tidak diperbolehkan kosong.',
            'password_confirmation.min' => 'Konfirmasi kata sandi minimal terdiri dari 8 karakter.',
            'kode_otp.required' => 'OTP tidak diperbolehkan kosong.',
            'kode_otp.numeric' => 'OTP yang valid hanya diperbolehkan mengandung angka.',
            'kode_otp.digits' => 'Kode OTP harus terdiri dari 6 digit.'
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

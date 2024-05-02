<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required_with:password',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required_with:password'
        ];
    }

    public function messages()
    {
        return [
            'current_password.required_with' => 'Kata sandi lama tidak diperbolehkan kosong.',
            'password.required' => 'Kata sandi baru tidak diperbolehkan kosong.',
            'password.min' => 'Kata sandi baru terlalu pendek, minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai dengan kata sandi baru anda.',
            'password_confirmation.required_with' => 'Konfirmasi kata sandi tidak diperbolehkan kosong.',
        ];
    }
}

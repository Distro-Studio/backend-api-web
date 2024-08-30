<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendingOTPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:data_karyawans,email']
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Silahkan masukkan email anda terlebih dahulu.',
            'email.email' => 'Format email yang diperbolehkan menggunakan @gmail atau yang lainnya.',
            'email.exists' => 'Email anda tidak terdaftar pada sistem kami.'
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

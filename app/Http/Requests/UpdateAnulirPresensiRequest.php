<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAnulirPresensiRequest extends FormRequest
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
            'alasan' => 'required',
            'dokumen' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'alasan.required' => 'Alasan tidak diperbolehkan kosong.',
            'dokumen.file' => 'Dokumen anulir harus berupa file.',
            'dokumen.max' => 'Dokumen anulir tidak boleh lebih dari 10 MB.',
            'dokumen.mimes' => 'Dokumen anulir harus berupa file PDF, JPEG, PNG, atau JPG.',
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

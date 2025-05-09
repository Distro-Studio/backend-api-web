<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenyesuaianGajiCustomRequest extends FormRequest
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
            'nama_detail' => 'required|string',
            'besaran' => 'required|numeric',
            'bulan_mulai' => 'nullable|string',
            'bulan_selesai' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'nama_detail.required' => 'Nama pengurang gaji tidak diperbolehkan kosong.',
            'nama_detail.string' => 'Nama pengurang gaji tidak diperbolehkan mengandung angka.',
            'besaran.required' => 'Besaran pengurang gaji tidak diperbolehkan kosong.',
            'besaran.numeric' => 'Besaran pengurang gaji tidak diperbolehkan mengandung huruf.',
            'bulan_mulai.string' => 'Data tanggal mulai hanya diperbolehkan mengandung huruf dan angka.',
            'bulan_selesai.string' => 'Data tanggal selesai hanya diperbolehkan mengandung huruf dan angka.',
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

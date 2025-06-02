<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAnulirPresensiRequest extends FormRequest
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
            'data_karyawan_id' => 'required|integer|exists:data_karyawans,id',
            'presensi_id' => 'required|integer|exists:presensis,id',
            'alasan' => 'required',
            'dokumen' => 'nullable|max:10240|mimes:jpeg,png,jpg,pdf',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'data_karyawan_id.required' => 'Data karyawan tidak diperbolehkan kosong.',
            'data_karyawan_id.integer' => 'Data karyawan harus berupa angka.',
            'data_karyawan_id.exists' => 'Data karyawan terkait tidak ditemukan.',
            'presensi_id.required' => 'Presensi tidak diperbolehkan kosong.',
            'presensi_id.integer' => 'Presensi harus berupa angka.',
            'presensi_id.exists' => 'Presensi terkait tidak ditemukan.',
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

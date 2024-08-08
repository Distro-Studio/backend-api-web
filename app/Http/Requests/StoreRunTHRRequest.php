<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRunTHRRequest extends FormRequest
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
            'data_karyawan_ids' => 'required|array',
            'data_karyawan_ids.*' => 'exists:data_karyawans,id',
            'tgl_run_thr' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'data_karyawan_ids.required' => 'Silahkan pilih nama karyawan terlebih dahulu.',
            'data_karyawan_ids.array' => 'Nama karyawan yang dipilih harus berupa array.',
            'data_karyawan_ids.*.exists' => 'Nama karyawan yang dipilih tidak valid.',
            'tgl_run_thr.required' => 'Penetapan tanggal THR tidak diperbolehkan kosong.',
            'tgl_run_thr.string' => 'Penetapan tanggal THR hanya diperbolehkan menggunakan angka dan huruf.',
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

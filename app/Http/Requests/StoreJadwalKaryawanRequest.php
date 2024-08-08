<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJadwalKaryawanRequest extends FormRequest
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
            'user_id' => 'required|array',
            'user_id.*' => 'integer|exists:users,id',
            'tgl_mulai' => 'required|string',
            // 'tgl_selesai' => 'nullable|string',
            'shift_id' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih nama karyawan terlebih dahulu.',
            'user_id.array' => 'Nama karyawan tidak diperbolehkan mengandung format selain array.',
            'user_id.*.integer' => 'Nama jabatan tidak diperbolehkan mengandung selain angka.',
            'user_id.*.exists' => 'Nama karyawan tersebut tidak valid.',
            'tgl_mulai.required' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan kosong.',
            'tgl_mulai.string' => 'Tanggal mulai jadwal karyawan yang diperbolehkan berupa angka dan teks.',
            // 'tgl_selesai.string' => 'Tanggal selesai jadwal karyawan yang diperbolehkan berupa angka dan teks.',
            // 'shift_id.required' => 'Silahkan pilih shift jadwal karyawan terlebih dahulu.',
            'shift_id.integer' => 'Shift jadwal karyawan tidak diperbolehkan mengandung selain angka.',
            'shift_id.exists' => 'Shift jadwal karyawan tersebut tidak valid.',
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

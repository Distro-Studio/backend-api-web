<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCutiJadwalRequest extends FormRequest
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
            // 'user_id' => 'required|integer|exists:users,id',
            'tipe_cuti_id' => 'required|integer|exists:tipe_cutis,id',
            'tgl_from' => 'required|string',
            'tgl_to' => 'required|string',
            'catatan' => 'nullable|string',
            // 'status_cuti_id' => 'required|integer|exists:status_cutis,id',
        ];
    }

    public function messages()
    {
        return [
            // 'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            // 'user_id.exists' => 'Nama karyawan yang dipilih tidak valid.',
            'tipe_cuti_id.required' => 'Silahkan pilih tipe cuti yang tersedia terlebih dahulu.',
            'tipe_cuti_id.exists' => 'Tipe cuti yang dipilih tidak valid.',
            'tgl_from.required' => 'Tanggal mulai cuti karyawan tidak diperbolehkan kosong.',
            'tgl_from.string' => 'Tanggal mulai cuti karyawan tidak diperbolehkan selain angka atau huruf.',
            'tgl_to.required' => 'Tanggal selesai cuti karyawan tidak diperbolehkan kosong.',
            'tgl_to.string' => 'Tanggal selesai cuti karyawan tidak diperbolehkan selain angka atau huruf.',
            'catatan.string' => 'Catatan cuti karyawan tidak diperbolehkan selain angka atau huruf.',
            'status_cuti_id.required' => 'Silahkan pilih status cuti yang tersedia terlebih dahulu.',
            'status_cuti_id.exists' => 'Status cuti yang dipilih tidak valid.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCutiJadwalRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'tipe_cuti_id' => 'required|integer|exists:tipe_cutis,id',
            'tgl_from' => 'required|date',
            'tgl_to' => 'required|date',
            'durasi' => 'required|numeric',
            'catatan' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_id.exists' => 'Maaf karyawan yang dipilih tidak valid.',
            'tipe_cuti_id.required' => 'Silahkan pilih tipe cuti yang tersedia terlebih dahulu.',
            'tipe_cuti_id.exists' => 'Maaf tipe cuti yang dipilih tidak valid.',
            'tgl_from.required' => 'Tanggal mulai cuti karyawan tidak diperbolehkan kosong.',
            'tgl_from.date' => 'Tanggal mulai cuti karyawan harus berupa tanggal.',
            'tgl_to.required' => 'Tanggal selesai cuti karyawan tidak diperbolehkan kosong.',
            'tgl_to.date' => 'Tanggal selesai cuti karyawan harus berupa tanggal.',
            'durasi.required' => 'Durasi cuti karyawan tidak diperbolehkan kosong.',
            'durasi.numeric' => 'Durasi cuti karyawan harus berupa angka.',
            'catatan.string' => 'Catatan cuti karyawan harus berupa teks.',
            'catatan.max' => 'Catatan cuti karyawan tidak diperbolehkan lebih dari 255 karakter.',
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

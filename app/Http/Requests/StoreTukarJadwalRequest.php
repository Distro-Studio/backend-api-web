<?php

namespace App\Http\Requests;

use App\Models\Shift;
use App\Models\Jadwal;
use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTukarJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_pengajuan' => 'required|exists:users,id',
            'jadwal_pengajuan' => 'required|exists:jadwals,id',
            'user_ditukar' => 'required|exists:users,id',
            'jadwal_ditukar' => 'required|exists:jadwals,id'
        ];
    }

    public function messages()
    {
        return [
            'user_pengajuan.required' => 'Silahkan pilih karyawan yang tersedia terlebih dahulu.',
            'user_pengajuan.exists' => 'Data pengguna yang dipilih tidak valid.',
            'jadwal_pengajuan.required' => 'Silahkan pilih shift yang tersedia terlebih dahulu.',
            'jadwal_pengajuan.exists' => 'Data shift yang dipilih tidak valid.',
            'user_ditukar.required' => 'Silahkan pilih karyawan yang tersedia dan ingin ditukar terlebih dahulu.',
            'user_ditukar.exists' => 'Data karyawan yang dipilih tidak valid.',
            'jadwal_ditukar.required' => 'Silahkan pilih shift yang tersedia dan ingin ditukar terlebih dahulu.',
            'jadwal_ditukar.exists' => 'Data shift yang dipilih tidak valid.'
        ];
    }
    
    public function failedValidation(Validator $validator)
    {
        $response = [
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => $validator->errors()
        ];

        throw new HttpResponseException(response()->json($response, Response::HTTP_BAD_REQUEST));
    }
}

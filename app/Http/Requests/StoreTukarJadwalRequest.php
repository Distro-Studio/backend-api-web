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
        $rules = [
            'user_pengajuan' => 'required|exists:users,id',
            'jadwal_pengajuan' => 'required|exists:jadwals,id',
            'user_ditukar' => 'required|exists:users,id',
            'jadwal_ditukar' => 'required|exists:jadwals,id',
            'tgl_mulai_ditukar' => 'nullable|date',
        ];

        if ($this->isLiburShift()) {
            $rules['tgl_mulai_ditukar'] = 'required|date';
        }

        return $rules;
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
            'jadwal_ditukar.exists' => 'Data shift yang dipilih tidak valid.',
            'tgl_mulai_ditukar.required' => 'Tanggal mulai tidak diperbolehkan kosong dan diperlukan untuk Shift Libur.',
            'tgl_mulai_ditukar.date' => 'Tanggal yang valid harus berupa tanggal.',
        ];
    }

    protected function isLiburShift()
    {
        $jadwalPengajuan = Jadwal::find($this->jadwal_pengajuan);
        $jadwalDitukar = Jadwal::find($this->jadwal_ditukar);

        if (!$jadwalPengajuan || !$jadwalDitukar) {
            return false;
        }

        $shiftPengajuan = Shift::find($jadwalPengajuan->shift_id);
        $shiftDitukar = Shift::find($jadwalDitukar->shift_id);

        return $shiftPengajuan && $shiftDitukar && $shiftPengajuan->nama == 'Libur' && $shiftDitukar->nama == 'Libur';
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

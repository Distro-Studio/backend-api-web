<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLemburKaryawanRequest extends FormRequest
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
            'tgl_pengajuan' => 'required|date',
            'durasi_jam' => 'required|numeric',
            'durasi_menit' => 'required|numeric',
            'catatan' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_pengajuan.required' => 'Silahkan pilih tanggal pengajuan terlebih dahulu.',
            'tgl_pengajuan.date' => 'Format tanggal pengajuan harus berupa tanggal.',
            'durasi_jam.required' => 'Durasi lembur karyawan tidak diperbolehkan kosong.',
            'durasi_jam.numeric' => 'Durasi lembur karyawan harus berupa angka.',
            'durasi_menit.required' => 'Durasi lembur karyawan tidak diperbolehkan kosong.',
            'durasi_menit.numeric' => 'Durasi lembur karyawan harus berupa angka.',
            'catatan.required' => 'Catatan lembur karyawan tidak diperbolehkan kosong.',
            'catatan.string' => 'Catatan lembur karyawan tidak diperbolehkan mengandung angka atau karakter lainnya.',
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

<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJadwalShiftKaryawanRequest extends FormRequest
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
            'tgl_mulai' => 'required|string',
            'shift_id' => 'required|integer',
            'ex_libur' => 'nullable|boolean'
        ];
    }

    public function messages()
    {
        return [
            'tgl_mulai.required' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan kosong.',
            'tgl_mulai.string' => 'Tanggal mulai jadwal karyawan tidak diperbolehkan mengandung selain angka dan huruf.',
            'shift_id.required' => 'Silahkan pilih shift jadwal karyawan terlebih dahulu.',
            'shift_id.integer' => 'Shift jadwal karyawan tidak diperbolehkan mengandung selain angka.',
            // 'shift_id.exists' => 'Shift jadwal karyawan tersebut tidak valid.',
            'ex_libur.boolean' => 'Extra Libur jadwal karyawan tidak diperbolehkan mengandung selain angka.',
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

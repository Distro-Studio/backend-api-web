<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUnitKerjaRequest extends FormRequest
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
            'nama_unit' => 'required|string|max:225|unique:unit_kerjas,nama_unit',
            'jenis_karyawan' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'nama_unit.required' => 'Nama Unit Kerja tidak diperbolehkan kosong.',
            'nama_unit.max' => 'Nama Unit Kerja melebihi batas maksimum panjang karakter.',
            'nama_unit.unique' => 'Nama Unit Kerja tersebut sudah pernah dibuat.',
            'jenis_karyawan.required' => 'Jenis Karyawan tidak diperbolehkan kosong.',
            'jenis_karyawan.string' => 'Jenis Karyawan tidak diperbolehkan mengandung angka.',
            'jenis_karyawan.boolean' => 'Jenis Karyawan hanya dapat diisi Shift atau Non-Shift.',
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

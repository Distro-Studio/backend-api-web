<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCutiRequest extends FormRequest
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
            'nama' => 'required|string|max:255|unique:tipe_cutis,nama',
            'kuota' => 'required|integer',
            'is_need_requirement' => 'required|boolean',
            'keterangan' => 'required|string|max:255',
            'cuti_administratif' => 'required|boolean',
            'is_unlimited' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama cuti tidak diperbolehkan kosong.',
            'nama.string' => 'Nama cuti tidak diperbolehkan mengandung karakter selain huruf.',
            'nama.max' => 'Nama cuti melebihi batas maksimum panjang karakter.',
            'nama.unique' => 'Nama cuti tersebut sudah pernah dibuat.',
            'kuota.required' => 'Kuota cuti tidak diperbolehkan kosong.',
            'kuota.integer' => 'Kuota cuti tidak diperbolehkan mengandung karakter selain angka.',
            'is_need_requirement.required' => 'Persyaratan cuti tidak diperbolehkan kosong.',
            'is_need_requirement.boolean' => 'Persyaratan cuti harus berupa boolean.',
            'keterangan.required' => 'Keterangan cuti tidak diperbolehkan kosong.',
            'keterangan.string' => 'Keterangan cuti diperbolehkan mengandung karakter selain huruf.',
            'keterangan.max' => 'Keterangan melebihi batas maksimum panjang karakter.',
            'cuti_administratif.required' => 'Cuti absensi tidak boleh kosong.',
            'cuti_administratif.boolean' => 'Cuti absensi harus berupa boolean.',
            'is_unlimited.required' => 'Cuti yang tak terbatas tidak boleh kosong.',
            'is_unlimited.boolean' => 'Cuti yang tak terbatas harus berupa boolean.',
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

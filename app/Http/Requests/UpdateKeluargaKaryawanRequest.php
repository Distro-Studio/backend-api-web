<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKeluargaKaryawanRequest extends FormRequest
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
            'nama_keluarga' => 'required|string|max:225',
            'hubungan' => 'required|string',
            'pendidikan_terakhir' => 'required',
            'pekerjaan' => 'string',
            'status_hidup' => 'required|boolean',
            'no_hp' => 'numeric',
            'email' => 'email',
        ];
    }

    public function messages()
    {
        return [
            'nama_keluarga.required' => 'Nama keluarga tidak diperbolehkan kosong.',
            'nama_keluarga.string' => 'Nama keluarga tidak diperbolehkan mengandung angka.',
            'nama_keluarga.max' => 'Nama keluarga melebihi batas maksimum panjang karakter.',
            'hubungan.required' => 'Hubungan keluarga tidak diperbolehkan kosong.',
            'hubungan.string' => 'Hubungan keluarga tidak diperbolehkan mengandung angka.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir dari keluarga terkait tidak diperbolehkan kosong.',
            // 'pekerjaan.required' => 'Pekerjaan keluarga terkait tidak diperbolehkan kosong.',
            'pekerjaan.string' => 'Pekerjaan keluarga terkait tidak diperbolehkan mengandung angka.',
            'status_hidup.required' => 'Status hidup keluarga terkait tidak diperbolehkan kosong.',
            'status_hidup.boolean' => 'Status hidup hanya bisa diisi hidup atau meninggal.',
            // 'no_hp.required' => 'Nomor telepon keluarga terkait tidak diperbolehkan kosong.',
            'no_hp.numeric' => 'Nomor telepon tidak diperbolehkan mengandung huruf.',
            // 'email.required' => 'Email tidak diperbolehkan kosong.',
            'email.email' => 'Email yang valid adalah email yang mengandung tanda @.',
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

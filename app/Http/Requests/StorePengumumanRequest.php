<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePengumumanRequest extends FormRequest
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
    public function rules()
    {
        return [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tgl_mulai' => 'required|string',
            'tgl_berakhir' => 'required|string',
            'user_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'judul.required' => 'Judul pengumuman tidak diperbolehkan kosong.',
            'konten.required' => 'Konten pengumuman tidak diperbolehkan kosong.',
            'tgl_mulai.required' => 'Tanggal mulai pengumuman tidak diperbolehkan kosong.',
            'tgl_berakhir.required' => 'Tanggal berakhir pengumuman tidak diperbolehkan kosong.',
            'user_id.required' => 'Anda harus memilih setidaknya satu karyawan untuk membuat pengumuman.',
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

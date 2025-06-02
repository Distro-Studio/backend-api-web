<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAboutHospitalRequest extends FormRequest
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
            'konten' => 'required|string',
            'about_hospital_1' => $this->hasFile('about_hospital_1')
                ? 'nullable|image|max:10240|mimes:jpg,jpeg,png,svg'
                : 'nullable',
            'about_hospital_1' => $this->hasFile('about_hospital_2')
                ? 'nullable|image|max:10240|mimes:jpg,jpeg,png,svg'
                : 'nullable',
            'about_hospital_1' => $this->hasFile('about_hospital_3')
                ? 'nullable|image|max:10240|mimes:jpg,jpeg,png,svg'
                : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'konten.required' => 'Isi konten tidak diperbolehkan kosong.',
            'konten.string' => 'Isi konten tidak diperbolehkan mengandung selain angka dan huruf.',
            'dokumen.file' => 'Gambar konten harus berupa gambar.',
            'dokumen.max' => 'Gambar konten maksimal 10 MB.',
            'dokumen.mimes' => 'Gambar konten harus berupa JPG, JPEG, PNG, atau SVG.',
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

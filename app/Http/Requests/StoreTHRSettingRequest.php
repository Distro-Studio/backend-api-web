<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTHRSettingRequest extends FormRequest
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
        $rules = [
            'perhitungan' => 'required|string',
            'nominal_satu' => 'nullable|numeric',
            'nominal_dua' => 'nullable|numeric',
            'potongan' => 'required|string',
            'tahun' => 'nullable|integer|min:0',
            'bulan' => 'required|integer|min:0|max:12',
        ];

        if ($this->input('perhitungan') === 'gaji_pokok_custom_nominal') {
            $rules['nominal_satu'] = 'required|numeric';
        }

        // TODO: ini nanti dulu | lom clear
        // elseif ($this->input('perhitungan') === 'full_custom_nominal') {
        //     $rules['nominal_satu'] = 'required|numeric';
        //     $rules['nominal_dua'] = 'required|numeric';
        // }

        return $rules;
    }

    public function messages()
    {
        return [
            'perhitungan.required' => 'Silahkan pilih perhitungan THR yang diperlukan.',
            'perhitungan.string' => 'Nilai perhitungan THR tidak diperbolehkan mengandung angka.',
            'nominal_satu.required' => 'Nilai nominal rupiah tidak diperbolehkan kosong.',
            'nominal_satu.numeric' => 'Nilai nominal rupiah tidak diperbolehkan mengandung huruf.',
            'nominal_dua.required' => 'Nilai nominal rupiah tidak diperbolehkan kosong.',
            'nominal_dua.numeric' => 'Nilai nominal rupiah tidak diperbolehkan mengandung huruf.',
            'potongan.required' => 'Silahkan pilih pajak dan premi yang diperlukan.',
            'potongan.string' => 'Nilai pajak dan premi tidak diperbolehkan mengandung angka.',
            'tahun.integer' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan mengandung huruf.',
            'tahun.min' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan kurang dari 0 tahun.',
            'bulan.required' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan kosong.',
            'bulan.integer' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan mengandung huruf.',
            'bulan.min' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan kurang dari 0 bulan.',
            'bulan.max' => 'Jumlah lamanya bekerja karyawan kontrak tidak diperbolehkan lebih dari 12 bulan.',
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

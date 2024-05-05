<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreTERRequest extends FormRequest
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
            'kategori_ter_id' => 'required',
            'ptkp_id' => 'required',
            'from_ter' => 'required|numeric',
            'to_ter' => 'required|numeric',
            'percentage_ter' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'kategori_ter_id.required' => 'Silahkan pilih kategori TER terlebih dahulu.',
            'ptkp_id.required' => 'Silahkan pilih PTKP terlebih dahulu.',
            'from_ter.required' => 'Batas penghasilan awal tidak diperbolehkan kosong.',
            'from_ter.numeric' => 'Batas penghasilan awal tidak diperbolehkan mengandung huruf.',
            'to_ter.required' => 'Batas penghasilan akhir tidak diperbolehkan kosong.',
            'to_ter.numeric' => 'Batas penghasilan akhir tidak diperbolehkan mengandung huruf.',
            'percentage_ter.required' => 'Persentase TER tidak diperbolehkan kosong.',
            'percentage_ter.numeric' => 'Persentase TER tidak diperbolehkan mengandung huruf.',
        ];
    }
}

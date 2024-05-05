<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalPenggajianRequest extends FormRequest
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
            'tanggal' => 'required|date'
        ];
    }

    public function messages()
    {
        return [
            'tanggal.required' => 'Tanggal penjadwalan gaji tidak diperbolehkan kosong.',
            'tanggal.date' => 'Tanggal penjadwalan gaji wajib berisi tanggal.'
        ];
    }
}

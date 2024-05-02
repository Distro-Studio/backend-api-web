<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            'name' => 'required|string|max:225|unique:roles,name',
            'description' => 'string|max:225|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama Role tidak diperbolehkan kosong.',
            'name.string' => 'Nama Role tidak diperbolehkan mengandung angka.',
            'name.max' => 'Nama Role melebihi batas maksimum panjang karakter.',
            'name.unique' => 'Nama Role tersebut sudah pernah dibuat.',
            'description.string' => 'Deskripsi Role tidak diperbolehkan mengandung angka.',
            'description.max' => 'Deskripsi Role melebihi batas maksimum panjang karakter.',
        ];
    }
}

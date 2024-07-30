<?php

namespace App\Http\Resources\Dashboard\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public $status;
    public $message;
    public $data;

    public function __construct($status, $message, $data)
    {
        parent::__construct($data);
        $this->status = $status;
        $this->message = $message;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data'  => [
                'id' => $this->id,
                'nama' => $this->nama,
                // 'username' => $this->username,
                'email' => $this->data_karyawans ? $this->data_karyawans->email : null,
                'foto_profil' => $this->foto_profil,
                'status_aktif' => $this->status_aktif,
                'data_completion_step' => $this->data_completion_step,
                'role' => $this->roles,
                'permissions' => $this->getAllPermissions(),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}

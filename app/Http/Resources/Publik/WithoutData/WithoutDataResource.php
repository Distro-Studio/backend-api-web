<?php

namespace App\Http\Resources\Publik\WithoutData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithoutDataResource extends JsonResource
{
    public $status;
    public $message;

    public function __construct($status, $message)
    {
        parent::__construct(null);
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
        ];
    }
}

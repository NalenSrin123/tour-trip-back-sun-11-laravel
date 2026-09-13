<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'guide_id'   => $this->guide_id,
            'full_name'  => $this->full_name,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}

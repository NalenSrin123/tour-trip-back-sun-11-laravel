<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'tours_count' => $this->whenCounted('tours'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
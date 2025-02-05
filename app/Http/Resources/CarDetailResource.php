<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'rent_price' => $this->rent_price,
            'no_plat' => $this->no_plat,
            'status' => $this->status,
            'image' => $this->image,
            'created_at' => date_format($this->created_at, "D, d-m-y"), 
            'updated_at' => date_format($this->updated_at, "D, d-m-y"), 
        ];
    }
}

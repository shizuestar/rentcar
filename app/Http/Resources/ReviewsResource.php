<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "rating" => $this->rating,
            "comment" => $this->comment,
            'user' => $this->whenLoaded('user', function(){
                return [
                    "id" => $this->user->id,
                    "name" => $this->user->name,
                    "email" => $this->user->email,
                    "role" => $this->user->role
                ];
            }),
            'car' => $this->whenLoaded('car', function(){
                return [
                    "id" => $this->user->id,
                    "name" => $this->car->name,
                    "type" => $this->car->type,
                    "no_plat" => $this->car->no_plat,
                    "rent_price" => $this->car->rent_price,
                    "status" => $this->car->status,
                    "image" => $this->car->image,
                ];
            }),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
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
            "rent_date" => $this->rent_date,
            "return_date" => $this->return_date,
            "total" => $this->total,
            "status" => $this->status,
            "user_id" => $this->user_id,
            "car_id" => $this->car_id,
            'created_at' => date_format($this->created_at, "D, d-m-y"), 
            'updated_at' => date_format($this->updated_at, "D, d-m-y"),
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
            'payment' => $this->whenLoaded('payment', function(){
                return [
                    "id" => $this->payment->id,
                    "method" => $this->payment->method,
                    "amount" => $this->payment->amount,
                    "payment_proof" => $this->payment->payment_proof,
                    "status" => $this->payment->status,
                ];
            }),
        ];
    }
}

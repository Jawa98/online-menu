<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'order_id'    => $this->pivot->order_id,
            'product_id'  => $this->pivot->product_id,    
            'quantity'    => $this->pivot->quantity,    
            'price'       => $this->pivot->price,
            'currency_id' => $this->pivot->currency_id,   
        ]; 
    }
}


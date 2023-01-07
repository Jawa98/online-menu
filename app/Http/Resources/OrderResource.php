<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'id'           => $this->id,
            'customer_id'  => $this->customer_id,
            'business_id'  => $this->business_id,    
            'status'       => $this->status,
            'address'      => $this->address,
            'mobile'       => $this->mobile,
            'items'        => OrderItemResource::collection($this->resource->items),
            'total_price'  => $this->total_price,
            'currency_id'  => $this->currency_id,
        ];   
    }
}





<?php

namespace App\Http\Resources;

use App\Traits\HasTranslation;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->resource->product->loadTranslations();
        return [
            'id'          => $this->id,
            'product_id'  => $this->product_id,    
            'business_id' => $this->business_id,
            'price'       => $this->price,
            'currency_id' => $this->currency_id,
            'expire_at'   => $this->expire_at, 
            'product'     => new ProductResource($this->resource->product),
        ];     
    }
}


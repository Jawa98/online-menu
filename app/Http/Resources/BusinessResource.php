<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
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
            'id'               => $this->id,
            'code'             => $this->code,
            'subscription_at'  => $this->subscription_at,
            'expire_at'        => $this->expire_at,
            'country_id'       => $this->country_id,
            'city_id'          => $this->city_id,
            'api_key'          => $this->api_key,
            'name'             => $this->name,
            'name_array'       => $this->name_array??null,
            'address'          => $this->address,
            'address_array'    => $this->address_array??null,
        ];
    }
}


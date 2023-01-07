<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'id'                => $this->id,
            'code'              => $this->code,
            'monthly_price'     => $this->monthly_price,
            'quarterly_price'   => $this->quarterly_price,
            'yearly_price'      => $this->yearly_price,
            'currency_id'       => $this->currency_id,
            'title'             => $this->title,
            'title_array'       => $this->title_array??null,
            'description'       => $this->description,
            'description_array' => $this->description_array??null,
        ]; 
    }
}



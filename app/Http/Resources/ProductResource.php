<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $media = [];
        foreach($this->media as $m)
            $media[] = $m->file_name;
        return [
            'id'                 => $this->id,
            'price'              => $this->price,
            'business_id'        => $this->business_id,
            'category_id'        => $this->category_id,
            'name'               => $this->name,
            'name_array'         => $this->name_array??null,
            'description'        => $this->description,
            'description_array'  => $this->description_array??null,
            'available'          => $this->available,
            'media'              => $media,
            'sort'               => $this->sort,          
        ]; 
    }
}


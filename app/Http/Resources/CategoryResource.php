<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'id'             => $this->id,
            'code'           => $this->code,
            'image'          => $this->image,
            'parent_id'      => $this->parent_id,
            'business_id'    => $this->business_id,
            'group_category' => $this->group_category,
            'name'           => $this->name,
            'name_array'     => $this->name_array??null,
            'subcategories'  => isset($this->subcategories)?CategoryResource::collection($this->subcategories):null,
            'sort'           => $this->sort,          
        ]; 
    }
}



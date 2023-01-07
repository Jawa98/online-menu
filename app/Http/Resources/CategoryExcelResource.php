<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryExcelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $business_languages = $request->app_languages->pluck('code')->toArray();
        $res = [];
        $res[] = $this->id;
        $res[] = $this->code;
        $res[] = $this->parent_id?$this->parent_id."":"";
        $res[] = $this->group_category?"1":"0";
        $res[] = $this->sort."";
        foreach($business_languages as $language)
            $res[] = $this->name_array[$language];
        return $res;
    }
}

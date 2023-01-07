<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductExcelResource extends JsonResource
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
        $res[] = $this->category_id."";
        $res[] = $this->price."";
        $res[] = $this->currency_id."";
        $res[] = $this->sort."";
        foreach($business_languages as $language)
            $res[] = $this->name_array[$language];
        foreach($business_languages as $language)
            $res[] = $this->description_array[$language];    

        return $res;  
    }
}

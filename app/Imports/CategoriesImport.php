<?php

namespace App\Imports;

use App\Http\Middleware\Language;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;

class CategoriesImport implements ToModel
{
    private $business;
    private $languages;

    public function __construct($business, $languages) {
        $this->business = $business;
        $this->languages = $languages;
    }
    
    public function model(array $row)
    {
        $i=0;
        $data = [];
        $data['id'] = $row[$i++]??null;
        $data['code'] = $row[$i++]??null;

        if(isset($row[$i]))
            $data['parent_id'] = $row[$i]??null;
        $i++;
        
        $data['group_category'] = $row[$i++]??null;
        $data['sort'] = $row[$i++]??null;
        
        $data['name'] = [];
        foreach($this->languages as $lang)
            $data['name'][$lang] = $row[$i++]??"";
        
        $Validator = Validator::make($data, [
            'id'              => ['exists:categories,id'],
            'code'            => ['required', 'string'],
            'name'            => ['required', 'array', Language::rule($this->languages)],
            'parent_id'       => ['exists:categories,id'],
            'group_category'  => ['required', 'boolean'],
            'sort'            => ['integer', 'min:0'],
        ]);
            
        if($Validator->fails())
            return;
            
        if(isset($data['parent_id']))
        {
            $parent = Category::where('id',$data['parent_id'])->first();
            if(!$parent->group_category)
                return;
        }

        if($data['id'])
        {
            if($category = Category::find($data['id'])){
                if($category->business_id != $this->business->id)
                    return;
                
                $category->updateWithTranslations([
                    'id'              => $data['id'],
                    'code'            => $data['code'],
                    'name'            => $data['name'],
                    'parent_id'       => $data['parent_id']??null,
                    'group_category'  => $data['group_category'],
                    'sort'            => $data['sort'],
                ], [], $this->languages);
            }
        }
        else
        {
            Category::createWithTranslations([
                'id'              => $data['id'],
                'code'            => $data['code'],
                'name'            => $data['name'],
                'image'           => null,
                'parent_id'       => $data['parent_id']??null,
                'business_id'     => $this->business->id,
                'group_category'  => $data['group_category'],
                'sort'            => $data['sort'],
            ], null, $this->languages);
        }
    }
}

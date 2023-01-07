<?php

namespace App\Imports;

use App\Http\Middleware\Language;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
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
        $data['category_id'] = $row[$i++]??null;
        $data['price'] = $row[$i++]??null;
        $data['currency_id'] = $row[$i++]??null;
        $data['sort'] = $row[$i++]??null;

        $data['name'] = [];
        foreach($this->languages as $lang)
            $data['name'][$lang] = $row[$i++]??"";

        $data['description'] = [];
        foreach($this->languages as $lang)
            $data['description'][$lang] = $row[$i++]??"";
        
        $Validator = Validator::make($data, [
            'id'              => ['exists:products,id'],
            'code'            => ['required', 'string'],
            'name'            => ['required', 'array', Language::rule($this->languages)],
            'description'     => ['required', 'array', Language::rule($this->languages)],
            'category_id'     => ['exists:categories,id'],
            'price'           => ['required', 'numeric', 'min:0'],
            'currency_id'     => ['required', 'exists:currencies,id'],
            'sort'            => ['integer', 'min:0'],
        ]);

        if($Validator->fails())
            return;      
         
        $category = Category::where('id',$data['category_id'])->first();
            
        if($category->group_category)
            return;

        if($data['id'])
        {
            if($product = Product::find($data['id']))
            {
                if($product->business_id != $this->business->id)
                    return;

                $product->updateWithTranslations([
                    'code'            => $data['code'],
                    'name'            => $data['name'],
                    'description'     => $data['description'],
                    'price'           => $data['price'],
                    'category_id'     => $data['category_id'],
                    'business_id'     => $this->business->id,
                    'currency_id'     => $data['currency_id'],
                    'sort'            => $data['sort'],
                ],[], $this->languages);
            }
        }
        else
        {
            Product::createWithTranslations([
                'code'            => $data['code'],
                'name'            => $data['name'],
                'description'     => $data['description'],
                'price'           => $data['price'],
                'category_id'     => $data['category_id'],
                'business_id'     => $this->business->id,
                'currency_id'     => $data['currency_id'],
                'sort'            => $data['sort'],
            ], null, $this->languages);
        }
    }
}

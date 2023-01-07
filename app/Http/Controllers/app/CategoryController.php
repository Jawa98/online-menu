<?php

namespace App\Http\Controllers\app;

use App\Exports\CategoryExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Http\Resources\CategoryExcelResource;
use App\Imports\CategoriesImport;
use App\Models\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('role:business_owner')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show','import']);

    }

    public function index(Request $request)
    {
        $_categories = Category::withTranslations()->where('business_id', $request->business->id)->get()->sortBy('sort');

        $categories = [];

        $categories += self::fill_categories($_categories, null);

        return CategoryResource::collection($categories);
    }

    private function fill_categories($source, $parent_id)
    {
        $categories = [];
        foreach($source as $cat)
        {
            if($cat->parent_id == $parent_id)
            {
                if($cat->group_category)
                    $cat->subcategories = self::fill_categories($source, $cat->id);
                $categories[] = $cat;
            }
        }
        return $categories;
    }

    public function store(Request $request)
    {
        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $business_languages = $request->app_languages->pluck('code')->toArray();
        $request->validate( [
            'code'            => ['required', 'string'],
            'name'            => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'parent_id'       => ['exists:categories,id'],
            'image'           => ['image'],
            'group_category'  => ['required', 'boolean'],
        ]);

        if($request->parent_id)
        {
            $parent = Category::where('id',$request->parent_id)->first();
            if(!$parent->group_category)
                return response()->json([
                    'message' => ___('custom.invalid_parent_id'),
                    'errors' => [
                        'parent_id' => [___('custom.invalid_parent_id')]
                        ]
                ], 422);
        }

        if($request->image)
            $image = fileStore('category', $request, 'image', 'categories');

        $category = Category::createWithTranslations([
            'code'            => $request->code,
            'name'            => $request->name,
            'image'           => $image??null,
            'parent_id'       => $request->parent_id,
            'business_id'     => $request->business->id,
            'group_category'  => $request->group_category,
        ], null, $business_languages);

        return response()->json(new CategoryResource($category), 200);
    }

    public function show(Request $request, Category $category)
    {
        if($request->business->id != $category->business_id)
            throw new AuthorizationException();

        $category->loadTranslations();
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $business_languages = $request->app_languages->pluck('code')->toArray();
        $request->validate( [
            'code'         => ['required', 'string'],
            'name'         => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'parent_id'    => ['exists:categories,id'],
            'image'        => ['image'],
        ]);

        if($request->parent_id){
            $parent = Category::where('id',$request->parent_id)->first();
            if(!$parent->group_category){
                return response()->json([
                    'message' =>  __('validation.exists', ['attribute' => 'parent']),
                    'errors' => [
                        'parent_id' => [__('validation.exists', ['attribute' => 'parent'])]
                    ]
                ], 422);
            };
        }

        if($request->image)
        {
            $image = fileStore('category', $request, 'image', 'categories');
            if(Storage::exists('public/'.$category->image))
                Storage::delete('public/'.$category->image);
        }

        $category->updateWithTranslations([
            'code'        => $request->code,
            'name'        => $request->name,
            'image'       => $image?? $category->image,
            'parent_id'   => $request->parent_id,
            'business_id' => $request->business->id,
        ],[], $business_languages);

        return response()->json(new CategoryResource($category), 200);
    }

    public function destroy(Request $request, Category $category)
    {
        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        Storage::delete('public/'.$category->image);
        $category->deleteWithTranslations();
        return response()->json(null, 204);
    }

    public function import(Request $request){
        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $business_languages = $request->app_languages->pluck('code')->toArray();

        $request->validate( [
            'file' => ['required', 'file', 'mimes:xlsx'],
        ]);

        Excel::import(new CategoriesImport($request->business, $business_languages), $request->file);
    }

    public function export(Request $request){
        $categories = Category::where('business_id', $request->business->id)->get();
        foreach($categories as $c)
            $c->loadTranslations();
        $_categories = CategoryExcelResource::collection($categories)->toArray($request);
        $header = [
            'id',
            'code',
            'parent_id',
            'group_category',
            'sort',
        ];
        $business_languages = $request->app_languages->pluck('code')->toArray();
        foreach($business_languages as $language)
            $header[] = "name ($language)";
        array_unshift($_categories,$header);
        $export = new CategoryExport($_categories);
        return Excel::download($export, 'categories.xlsx');
    }
}



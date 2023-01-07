<?php

namespace App\Http\Controllers\app;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductExcelResource;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show', 'get_product_media']);
        $this->middleware('role:business_owner')->except(['index','show', 'get_product_media']);
        $this->middleware('auth.type:user')->except(['index','show', 'get_product_media']);
    }

    public function index(Request $request)
    {
        $request->validate( [
            'category_id'     => ['exists:categories,id'],
        ]);

        if($request->category_id){
            $products = Product::with('media')->where('category_id',$request->category_id)
                               ->where('business_id', $request->business->id)
                               ->where('available', true)
                               ->get()->sortBy('sort');
        }
        else{
            $products = Product::with('media')->where('business_id', $request->business->id)
                                                   ->where('available', true)
                                                   ->get()->sortBy('sort');
        }
        foreach($products as $product)
            $product->loadTranslations();
        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$business_owner)
            throw new AuthorizationException();

        $business_languages = $request->app_languages->pluck('code')->toArray();
        $request->validate( [
            'code'            => ['required', 'string'],
            'name'            => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'description'     => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'category_id'     => ['exists:categories,id'],
            'price'           => ['required', 'numeric', 'min:0'],
            'currency_id'     => ['required', 'exists:currencies,id'],
        ]);

        $category = Category::where('id',$request->category_id)->first();

            if($category->group_category){
                return response()->json([
                    'message' =>  ___('custom.invalid_category_id'),
                    'errors' => [
                        'category_id' => [ ___('custom.invalid_category_id')]
                    ]
                ], 422);
            }

            $product = Product::createWithTranslations([
                'code'            => $request->code,
                'name'            => $request->name,
                'description'     => $request->description,
                'price'           => $request->price,
                'category_id'     => $request->category_id,
                'business_id'     => $request->business->id,
                'currency_id'     => $request->currency_id,
            ], null, $business_languages);

       return response()->json(new ProductResource($product), 200);
    }

    public function show(Request $request, Product $product)
    {
        if($request->business->id != $product->business_id)
            throw new AuthorizationException();

        if($product->available != true)
            throw new NotFoundHttpException();

        $product->with('media');
        $product->loadTranslations();
        return new ProductResource($product);
    }

    public function update(Request $request, Product $product)
    {
        $business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$business_owner)
            throw new AuthorizationException();

        $business_languages = $request->app_languages->pluck('code')->toArray();
        $request->validate( [
            'code'            => ['required', 'string'],
            'name'            => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'description'     => ['required', 'array', LanguageMiddleware::rule($business_languages)],
            'category_id'     => ['exists:categories,id'],
            'price'           => ['required', 'numeric', 'min:0'],
            'currency_id'     => ['required', 'exists:currencies,id'],
        ]);

        $category = Category::where('id',$request->category_id)->first();

            if($category->group_category){
                return response()->json([
                    'message' => __('validation.exists', ['attribute' => 'category']),
                    'errors' => [
                        'category_id' => [ __('validation.exists', ['attribute' => 'category'])]
                    ]
                ], 422);
            }
            $product->with('media');
            $product->updateWithTranslations([
                'code'            => $request->code,
                'name'            => $request->name,
                'description'     => $request->description,
                'price'           => $request->price,
                'category_id'       => $request->category_id,
                'business_id'     => $request->business->id,
                'currency_id'     => $request->currency_id,
            ],[], $business_languages);

        return response()->json(new ProductResource($product), 200);
    }

    public function destroy(Request $request, Product $product)
    {
        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $product->deleteWithTranslations();
        return response()->json(null, 204);
    }

    public function get_product_media(Product $product)
    {
        $media = ProductMedia::where('product_id', $product->id)->get();
        return MediaResource::collection($media);
    }

    public function add_product_media(Product $product, Request $request)
    {
        $request->validate( [
            'file_name'   => ['required', 'mimes:png,jpg,mp4,ogx,oga,ogv,ogg,webm'],
        ]);

        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $file_name = fileStore('product', $request, 'file_name', 'products');
        $media = new ProductMedia([
            'product_id' => $product->id,
            'file_name'  => $file_name,

        ]);
        $media->save();
        return response()->json(new MediaResource($media), 200);
    }


    public function remove_product_media(Product $product, Request $request)
    {

        $request->validate( [
            'media_id'   => ['required', 'exists:product_media,id'],
        ]);

        $this_business_owner = $request->business->owners->where('id', Auth::user()->id)->first();

        if(!$this_business_owner)
            throw new AuthorizationException();

        $media = ProductMedia::find($request->media_id);
        Storage::delete('public/'.$media->file_name);
        $media->delete();

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
        Excel::import(new ProductsImport($request->business, $business_languages), $request->file);
    }

    public function export(Request $request){
        $products = Product::where('business_id', $request->business->id)->where('available', true)->get();

        foreach($products as $p)
            $p->loadTranslations();
        $_products = ProductExcelResource::collection($products)->toArray($request);
        $header = [
            'id',
            'code',
            'category_id',
            'price',
            'currency_id',
            'sort',
        ];
        $business_languages = $request->app_languages->pluck('code')->toArray();
        foreach($business_languages as $language)
            $header[] = "name ($language)";
        foreach($business_languages as $language)
            $header[] = "description ($language)";
        array_unshift($_products,$header);
        $export = new ProductExport($_products);
        return Excel::download($export, 'products.xlsx');
    }
}

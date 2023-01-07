<?php

use App\Http\Controllers\app\AppController;
use App\Http\Controllers\app\AuthController;
use App\Http\Controllers\app\CategoryController;
use App\Http\Controllers\app\OfferController;
use App\Http\Controllers\app\OrderController;
use App\Http\Controllers\app\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('business' ,  [AppController::class, 'get_business']);
Route::get('languages',  [AppController::class, 'get_languages']);

Route::post('register',  [AuthController::class, 'register']);
Route::post('login'   ,  [AuthController::class, 'login']);
Route::post('logout'  ,  [AuthController::class, 'logout']);
Route::post('customer'  ,  [AuthController::class, 'customer']);
Route::get('username' ,  [AuthController::class, 'check_username']);

//Categories &products
Route::post('categories/import',[CategoryController::class, 'import']);
Route::post('categories/export',[CategoryController::class, 'export']);
Route::apiResource('categories' , CategoryController::class);
Route::post('products/import',[ProductController::class, 'import']);
Route::post('products/export',[ProductController::class, 'export']);
Route::apiResource('products' , ProductController::class);

Route::get('products/{product}/media', [ProductController::class, 'get_product_media']);
Route::post('products/{product}/media', [ProductController::class, 'add_product_media']);
Route::delete('products/{product}/media', [ProductController::class, 'remove_product_media']);

//owner orders
Route::apiResource('orders' , OrderController::class)->only(['index', 'store', 'show', 'update']);
Route::post('orders/{order}/accept', [OrderController::class, 'accept']);
Route::post('orders/{order}/reject', [OrderController::class, 'reject']);
Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::post('orders/{order}/deliver', [OrderController::class, 'deliver']);
Route::post('orders/{order}/complete', [OrderController::class, 'complete']);

//offers
Route::apiResource('offers' , OfferController::class);



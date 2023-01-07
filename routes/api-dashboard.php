<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\BusinessController;
use App\Http\Controllers\api\CityController;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\CountryController;
use App\Http\Controllers\api\CurrencyController;
use App\Http\Controllers\api\MessageController;
use App\Http\Controllers\api\OwnerOrderController;
use App\Http\Controllers\api\PlanController;
use App\Http\Controllers\api\SubscriptionController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

//Auth
Route::post('login'    , [AuthController::class, 'login']);
Route::post('logout'   , [AuthController::class, 'logout']);
Route::get('username'  , [AuthController::class, 'check_username']);

//users
Route::apiResource('users' , UserController::class);

//languages $ countries $cities $currencies $plans
Route::apiResource('languages' , LanguageController::class);
Route::apiResource('countries' , CountryController::class);
Route::apiResource('cities'    , CityController::class);
Route::apiResource('currencies', CurrencyController::class);
Route::apiResource('plans'     , PlanController::class);

//business
Route::apiResource('businesses' , BusinessController::class);
Route::get('businesses/me' , [BusinessController::class,'my_businesses']);

Route::get('businesses/{business}/owners',  [BusinessController::class, 'get_owners']);
Route::post('businesses/{business}/owners',  [BusinessController::class, 'add_owner']);
Route::delete('businesses/{business}/owners',  [BusinessController::class, 'remove_owner']);

Route::get('businesses/{business}/languages',  [BusinessController::class, 'get_languages']);
Route::post('businesses/{business}/languages',  [BusinessController::class, 'add_language']);
Route::delete('businesses/{business}/languages',  [BusinessController::class, 'remove_language']);

//subscriptions
Route::apiResource('subscriptions' , SubscriptionController::class);

//messages
Route::apiResource('messages', MessageController::class)->only(['index','update']);

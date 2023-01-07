<?php

use App\Http\Middleware\Language;
use Illuminate\Support\Facades\Cache;

$messages = json_decode(Cache::get('messages'),true)[Language::$language];

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'   => $messages['auth.failed'],
    'password' => $messages['auth.password'],
    'throttle' => $messages['auth.throttle'],

];

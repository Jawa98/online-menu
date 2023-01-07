<?php

use App\Http\Middleware\Language;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

if(!function_exists('to_user')){
    function to_user($user): User{
        return $user;
    }
}

if(!function_exists('to_customer')){
    function to_customer($customer): Customer{
        return $customer;
    }
}

if(!function_exists('fileStore')){
    function fileStore($title, $request, $nameRequest, $foldername)
        {
            if ($request->hasFile($nameRequest)) {
                $extension = $request->file($nameRequest)->getClientOriginalExtension();
                $imageToStore = $title . '_' . time() . '.' . $extension;
                $request->file($nameRequest)->storeAs('public/' . $foldername, $imageToStore);
        
                return $foldername.'/'.$imageToStore;
            }
        }
}

if(!function_exists('load_messages')){
    function load_messages($languages){
        $messages = [];
        foreach($languages as $language)
        {
            $_messages = Message::withTranslations($language)->get();
            foreach($_messages as $m)
                $messages[$language][$m->type.'.'.$m->key] = $m->value;
        }
        Cache::put('messages',json_encode($messages));
    }
}

if(!function_exists('___')){
    function ___($key){
        $messages = json_decode(Cache::get('messages',[]),true);
        if(isset($messages[Language::$language][$key]))
            return $messages[Language::$language][$key];
        if(isset($messages['en'][$key]))
            return $messages['en'][$key];
        return $key;
    }
}

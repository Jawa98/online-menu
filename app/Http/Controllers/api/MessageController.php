<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth.type:user');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $request->validate([
            'type'   => ['string'],
        ]);
        $query = Message::query();
        if($request->type)
            $query = $query->where('type', $request->type);
        $messages = $query->get();
        foreach($messages as $message)
            $message->loadTranslations();
        return MessageResource::collection($messages);
    }

    public function update(Request $request, Message $message)
    {
        $request->validate([
            'type'   => ['required','string'],
            'key'    => ['required', 'string'],
            'value'  => ['required', LanguageMiddleware::rule()],
        ]);

        $message->updateWithTranslations([
            'type'      => $request->type,
            'key'       => $request->key,
            'value'     => $request->value,
        ]);
        load_messages(LanguageMiddleware::$all_languages);
        return response()->json(new MessageResource($message), 200);
    }
}

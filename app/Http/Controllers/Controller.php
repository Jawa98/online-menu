<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function fileStore($title, $request, $nameRequest, $foldername)
    {
        if ($request->hasFile($nameRequest)) {
            $extension = $request->file($nameRequest)->getClientOriginalExtension();
            $imageToStore = $title . '_' . time() . '.' . $extension;
            $request->file($nameRequest)->storeAs('public/' . $foldername, $imageToStore);

            return $foldername.'/'.$imageToStore;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostController extends Controller
{
    public function WebGetHosts(): JsonResponse
    {
        $hosts = Host::query()->get();
        if(!$hosts->count() > 0)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ]);
        }
        return response()->json($hosts , 200);
    }
}

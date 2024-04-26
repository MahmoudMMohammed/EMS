<?php

namespace App\Http\Controllers;

use App\Models\MainEvent;
use Illuminate\Http\Request;

class MainEventController extends Controller
{
    public function GetEvents()
    {
        $events = MainEvent::query()->get();
        if(!$events)
        {
            return response()->json([
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }
        return response()->json($events , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}

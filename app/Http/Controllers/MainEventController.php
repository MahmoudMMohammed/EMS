<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\MainEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainEventController extends Controller
{
    public function GetEvents() :JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $events = MainEvent::query()->get();
        if(!$events->count() > 0)
        {
            return response()->json([
                'message' => 'Something went wrong , try again later' ,
                'status' => 400
            ] , 400);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $names = $events->pluck('name')->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);
        foreach ($events as $event){
            $responseData [] = [
                'id' => $event->id,
                'name' => $translatedNames[$event->name],
            ];
        }
        return response()->json($responseData , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////


}

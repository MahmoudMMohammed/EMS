<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\MainEventHost;
use App\Models\MEHAC;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessoryCategoryController extends Controller
{
    public function getAccessoriesCategory($event_id , $host_id): JsonResponse
    {

        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $exists = MainEventHost::query()->find($host_id);

        if (!$exists) {
            return response()->json(['error' => 'Host id is not found !'], 404);
        }

        $exists = MainEventHost::query()->find($event_id);

        if(!$exists){
            return response()->json(['error' => 'Event id is not found !'] , 404);
        }

        $Categories = MainEventHost::where('host_id' , $host_id)->where('main_event_id' , $event_id)->pluck('id');

        if(! $Categories->count() > 0)
        {
            return response()->json([
                    'message' => 'There are no category for this place']
                , 404);
        }

        $mehacs = MEHAC::query()->where('main_event_host_id' , $Categories)->get();

        if(! $Categories->count() > 0)
        {
            return response()->json([
                    'message' => 'Something went wrong , try again later']
                , 422);
        }

        $color = ['#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696' , '#443391' , '#3E5EAB' , '#1495CF' , '#60B246' , '#D1DC36' , '#F2EB3B' , '#F8BD19' , '#F89C21' , '#F25427' , '#F33128' , '#A71E4A' , '#7D3696'];
        $index = 0 ;

        $translate = [];
        foreach ($mehacs as $mehac)
        {
            $translate [] =  $mehac->category->category ;
        }

        $names = TranslateTextHelper::batchTranslate($translate);

        $response = [];
        foreach ($mehacs as $mehac)
        {
            $response [] = [
                'id' => $mehac->category->id ,
                'name' => $names[$mehac->category->category] ,
                'logo' => $mehac->category->logo ,
                'number' => $mehac->category->accessories->count() ,
                'color' => $color[$index]
            ] ;
            $index++ ;
        }
        return response()->json($response , 200);
    }
}

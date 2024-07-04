<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Location;
use App\Models\Search;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function getSearchHistory(): JsonResponse
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

        $histories = Search::query()
            ->where('user_id' , $user->id)
            ->orderBy('updated_at' , 'desc')
            ->get();

        if($histories->isEmpty())
        {
            return response()->json([
                'massage' => TranslateTextHelper::translate('There is no search history yet, dear user, search for anything (food - drinks - accessories - locations)') ,
                'status_code' => 404
            ]);
        }

        if($histories->count() > 12)
        {
            $historiesToDelete = $histories->slice(12);

            Search::destroy($historiesToDelete->pluck('id')->toArray());

            $histories = $histories->where('user_id' , $user->id)->take(12);
        }


        $response = [];
        foreach ($histories as $history)
        {
            $response [] = [
                'id' => $history->id ,
                'history' => $history->history
            ];
        }
        return response()->json($response , 200);
    }

    //////////////////////////////////////////////////////////////
    public function deleteOneSearch($history_id)
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $find = Search::query()->find($history_id);

        if(!$find)
        {
            return response()->json([
                'error' => 'invalid history id',
                'status_code' => 404,
            ], 404);
        }

        $result = Search::query()->where('user_id' , $user->id)->where('id' , $history_id)->first();

        $result->delete();

        return response()->json([
            'message' => TranslateTextHelper::translate('record deleted successfully'),
            'status_code' => 200,
        ], 200);

    }
    //////////////////////////////////////////////////////////////
    public function deleteAllSearch(): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $result = Search::query()->where('user_id' , $user->id)->get();

        if($result->isEmpty())
        {
            return response()->json([
                'massage' => TranslateTextHelper::translate('There is no search history yet to be deleted') ,
                'status_code' => 404
            ] , 404);
        }

        Search::query()->where('user_id', $user->id)->delete();

        return response()->json([
            'message' => TranslateTextHelper::translate('All search history deleted successfully.'),
            'status_code' => 200
        ], 200);

    }
    //////////////////////////////////////////////////////////////
    public function Search(Request $request): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all() , [
            'search' => 'required|string|max:100' ,
            'type' => 'required|string|in:food,drink,accessory,location'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()->first() ,
                'status_code' => 422
            ] , 422);
        }

        $input = $request->input('search');


        $exists = Search::query()->where('history' , $request->search)->first();

        if($exists) {
            //touch() : update the updated at value
            $exists->touch();
        }

        else {
            Search::query()->create([
                'user_id' => $user->id,
                'history' => $request->search
            ]);
        }

        $response = [];

        switch ($request->type){

            case 'food' :
                $results = Food::query()
                    ->where('name', 'LIKE', '%' . $input . '%')
                    ->orderBy('price')
                    ->get();

                if(!$results->count() > 0)
                {
                    return response()->json([
                        'massage' => TranslateTextHelper::translate("There are no search results in food category for ") . "($request->search)" ,
                        'status_code' => 404
                    ] , 404);
                }

                $names = $results->pluck('name')->toArray();
                $names = TranslateTextHelper::batchTranslate($names);

                $description = $results->pluck('description')->toArray();
                $description = TranslateTextHelper::batchTranslate($description);

                $country_of_origin = $results->pluck('country_of_origin')->toArray();
                $country_of_origin = TranslateTextHelper::batchTranslate($country_of_origin);

                foreach ($results as $result)
                {
                    $response [] = [
                        'id' => $result->id ,
                        'name' => $names[$result->name] ,
                        'price' => $result->price ,
                        'picture' => $result->picture ,
                        'description' => $description[$result->description] ,
                        'country_of_origin' => $country_of_origin[$result->country_of_origin] ,
                    ];
                }

                    break;

            case 'drink' :
                $results = Drink::query()
                    ->where('name' , 'LIKE' , '%' . $input . '%')
                    ->orderBy('price')
                    ->get();

                if(!$results->count() > 0)
                {
                    return response()->json([
                        'massage' => TranslateTextHelper::translate("There are no search results in drinks category for ") . "($request->search)" ,
                        'status_code' => 404
                    ] , 404);
                }

                $names = $results->pluck('name')->toArray();
                $names = TranslateTextHelper::batchTranslate($names);

                $description = $results->pluck('description')->toArray();
                $description = TranslateTextHelper::batchTranslate($description);

                foreach ($results as $result)
                {
                    $response [] = [
                        'id' => $result->id ,
                        'name' => $names[$result->name] ,
                        'price' => $result->price ,
                        'picture' => $result->picture ,
                        'description' => $description[$result->description]
                    ];
                }
                    break;

            case 'accessory' :
                $results = Accessory::query()
                    ->where('name' , 'LIKE' , '%' . $input . '%')
                    ->orderBy('price')
                    ->get();

                if(!$results->count() > 0)
                {
                    return response()->json([
                        'massage' => TranslateTextHelper::translate("There are no search results in accessories category for ") . "($request->search)" ,
                        'status_code' => 404
                    ] , 404);
                }

                $names = $results->pluck('name')->toArray();
                $names = TranslateTextHelper::batchTranslate($names);

                $description = $results->pluck('description')->toArray();
                $description = TranslateTextHelper::batchTranslate($description);

                foreach ($results as $result)
                {
                    $response [] = [
                        'id' => $result->id ,
                        'name' => $names[$result->name] ,
                        'picture' => $result->picture ,
                        'price' => $result->price ,
                        'description' => $description[$result->description]
                    ];
                }
                    break;

            case 'location' :
                $results = Location::query()
                    ->where('name' , 'LIKE' , '%' . $input . '%')
                    ->orderBy('reservation_price')
                    ->get();

                if(!$results->count() > 0)
                {
                    return response()->json([
                        'massage' => TranslateTextHelper::translate("There are no search results in locations category for ") . "($request->search)" ,
                        'status_code' => 404
                    ] , 404);
                }

                $names = $results->pluck('name')->toArray();
                $names = TranslateTextHelper::batchTranslate($names);

                $governorate = $results->pluck('governorate')->toArray();
                $governorate = TranslateTextHelper::batchTranslate($governorate);

                foreach ($results as $result)
                {
                    $response [] = [
                        'id' => $result->id ,
                        'name' => $names[$result->name] ,
                        'governorate' => $governorate[$result->governorate] ,
                        'open_time' => $result->open_time ,
                        'close_time' => $result->close_time
                    ];
                }

                    break;

            default :
                return response()->json(['error' => 'Invalid category' , 'status_code'=> 400], 400);
        }


        return response()->json($response , 200);
    }
}

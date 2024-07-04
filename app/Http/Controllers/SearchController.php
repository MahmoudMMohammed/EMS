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

        $histories = Search::query()->where('user_id' , $user->id)->orderBy('updated_at')->get();

        if($histories->isEmpty())
        {
            return response()->json([
                'massage' => TranslateTextHelper::translate('There is no search history yet, dear user, search for anything (food - drinks - accessories - locations)') ,
                'status_code' => 404
            ]);
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
    public function Search(Request $request)
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

        $input = TranslateTextHelper::translateToEnglishOnly($request->input('search'));

        $exists = Search::query()->where('history' , $request->search)->first();

        if($exists) {
            $exists->update([
                'updated_at' => Carbon::now()
            ]);
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
                        'name' => $names[$result->price] ,
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

                foreach ($results as $result)
                {
                    $response [] = [
                        'id' => $result->id ,
                        'name' => $result->name ,
                        'price' => $result->price ,
                        'picture' => $result->picture ,
                        'description' => $result->description
                    ];
                }
                    break;

            case 'accessory' :
                $results = Accessory::query()
                    ->where('name' , 'LIKE' , '%' . $input . '%')
                    ->orderBy('price')
                    ->get();
                    break;

            case 'location' :
                $results = Location::query()
                    ->where('name' , 'LIKE' , '%' . $input . '%')
                    ->orderBy('reservation_price')
                    ->get();
                    break;

            default :
                return response()->json(['error' => 'Invalid category' , 'status_code'=> 400], 400);
        }


    }
}

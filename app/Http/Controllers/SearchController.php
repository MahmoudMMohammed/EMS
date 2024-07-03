<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $histories = Search::query()->where('user_id' , $user->id)->get();

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
}

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

        $history = Search::query()->where('user_id' , $user->id)->get();

        if($history->isEmpty())
        {
            return response()->json([
                'massage' => TranslateTextHelper::translate('There is no search history yet, dear user, search for anything (food - drinks - accessories - locations)') ,
                'status_code' => 404
            ]);
        }

        return response()->json($history , 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\AppRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppRatingController extends Controller
{
    public function getUserAppRating() : JsonResponse
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

        $rate = AppRating::query()->where('user_id' , $user->id)->first();
        if(!$rate)
        {
            return response()->json(['rate' => 0] , 200);
        }
        return response()->json(['id' => $rate->id ,'rate' => $rate->rate] , 200);
    }
    ////////////////////////////////////////////////////////////////
    public function addAppRate(Request $request):JsonResponse
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

        $validator = Validator::make($request->all() , [
            'rate' => 'required|numeric|between:1,5' ,
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $rate = AppRating::query()->where('user_id' , $user->id)->first();

        if(!$rate)
        {
            AppRating::query()->create([
                'user_id' => $user->id ,
                'rate' => $request->input('rate'),
            ]);

            return response()->json([
                "message" => TranslateTextHelper::translate("Dear user, your rating has been added successfully. Thank you"),
                "status_code" => 201,
            ] , 201);

        }

        if($rate->rate == $request->input('rate'))
        {
            return response()->json([
                "message" => TranslateTextHelper::translate("Dear user, you have not made any changes, your rating remains the same"),
                "status_code" => 200,
            ], 200);
        }

        $rate->rate = $request->input('rate');
        $rate->save();

        return response()->json([
                "message" => TranslateTextHelper::translate("Dear user, your rating has been updated successfully"),
                "status_code" => 200,
        ] , 200);
    }
    ////////////////////////////////////////////////////////////////
    public function deleteAppRate($rate_id):JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = AppRating::query()->find($rate_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid rate id",
                "status_code" => 422,
            ], 422);
        }

        $exist->delete();
        return response()->json([
            "message" => TranslateTextHelper::translate("Dear user, your rating has been deleted successfully"),
            "status_code" => 200,
        ] , 200);
    }
}

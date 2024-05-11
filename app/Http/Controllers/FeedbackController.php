<?php

namespace App\Http\Controllers;


use App\Models\Feedback;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function GetRatingsLocation()
    {

    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function CreateFeedback(Request $request): JsonResponse
    {
        $validator = Validator::make( $request->all() , [
            "location_id" => 'required|exists:locations,id',
            "comment" => 'required_without:rate' ,
            "rate" => 'required_without:comment|regex:/^[1-9][0-9]*$/"|between:1,5'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $feedback = Feedback::query()->create([
            'user_id' => $user['id'],
            'location_id' => $request->location_id ,
            'comment' => $request->comment ,
            'rate' => $request->rate,
        ]);

        if(!$feedback)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            'message' => 'Your feedback add successfully , Thank you !' ,
            'status_code' => 201
        ] , 201);

    }
}

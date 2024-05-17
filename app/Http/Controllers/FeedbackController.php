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
            "rate" => 'required_without:comment|int|between:1,5'
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

        $existingFeedBack = Feedback::where('user_id' , $user['id'])->first();

        if($existingFeedBack)
        {
            return response()->json([
                'message' => 'You can not create a new feedback , you already have one !' ,
                'status_code'=> 400] , 400);
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

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetUserFeedBack(): JsonResponse
    {
        $user = Auth::user();

        $existingFeedBack = Feedback::whereUserId($user['id'])->first();

        if(!$existingFeedBack)
        {
            return response()->json([
                "error" => "This User dont have a feedback for this location !",
                "status_code" => 422,
            ], 422);
        }
        $responseData = [
            'id' => $existingFeedBack->id,
            'name' => $user->name ,
            'comment' => $existingFeedBack->comment ,
            'rate' => $existingFeedBack->rate
        ];


        return response()->json($responseData,200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetLocationRate($location_id): JsonResponse
    {

        $locationExists = Location::find($location_id);

        if (!$locationExists) {
            return response()->json(['error' => 'Location is not found !'], 404);
        }

        $ratings = Feedback::where('location_id', $location_id)->pluck('rate');

        $ratingCounts = array_fill(1, 5, 0);

        if ($ratings->isEmpty()) {
            $ratingPercentages = array_map(function () {
                return 0.0;
            }, $ratingCounts);

            $ratingPercentages['total_ratings'] = 0;
            $ratingPercentages['average_rating'] = 0.0;

            return response()->json($ratingPercentages , 200);
        }

        $totalRatings = $ratings->count();
        $sumOfRatings = $ratings->sum();

        foreach ($ratings as $rating) {
            if (isset($ratingCounts[$rating])) {
                $ratingCounts[$rating]++;
            }
        }

        $ratingPercentages = [];
        foreach ($ratingCounts as $rating => $count) {
            $ratingPercentages[$rating] = (double) number_format(($count / $totalRatings) * 100, 2);
        }

        $averageRating = $sumOfRatings / $totalRatings;

        $ratingPercentages['total_ratings'] = $totalRatings;
        $ratingPercentages['average_rating'] = $averageRating;

        return response()->json($ratingPercentages , 200);
    }

}

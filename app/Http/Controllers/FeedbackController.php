<?php

namespace App\Http\Controllers;


use App\Helpers\TranslateTextHelper;
use App\Models\Feedback;
use App\Models\Host;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{

    public function CreateFeedback(Request $request): JsonResponse
    {

        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile ->preferred_language);

        $validator = Validator::make( $request->all() , [
            "location_id" => 'required|exists:locations,id',
            "comment" => 'required_without:rate' ,
            "rate" => 'required_without:comment|numeric|between:0.5,5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $existingFeedBack = Feedback::where('user_id' , $user['id'])
                                    ->where('location_id', $request->location_id)
                                    ->first();

        if($existingFeedBack)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate('You can not create a new feedback , you already have one') ,
                'status_code'=> 400] , 400);
        }

        $feedback = Feedback::query()->create([
            'user_id' => $user['id'],
            'location_id' => $request->location_id ,
            'comment' => $request->comment ,
            'rate' => $request->rate ,
            'date'=> now()->format('Y-m-d H:i:s') ,
        ]);

        if(!$feedback)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            'message' => TranslateTextHelper::translate('Your feedback add successfully , Thank you') ,
            'status_code' => 201
        ] , 201);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetCurrentUserFeedBack($id): JsonResponse
    {
        $user = Auth::user();

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $existingFeedBack = Feedback::whereUserId($user['id'])->where('location_id' , $id)->first();

        if(!$existingFeedBack)
        {
            return response()->json([
                "error" => "This User dont have a feedback for this location !",
                "status_code" => 422,
            ], 422);
        }

        $feedbackDate = Carbon::parse($existingFeedBack->date);
        $now = Carbon::now();
        $diffInSeconds = $feedbackDate->diffInSeconds($now);
        $diffInMinutes = $feedbackDate->diffInMinutes($now);
        $diffInHours = $feedbackDate->diffInHours($now);
        $diffInDays = $feedbackDate->diffInDays($now);

        if ($diffInSeconds < 60) {
            $formattedDate =  TranslateTextHelper::translate($diffInSeconds . ' seconds ago');
        } elseif ($diffInMinutes < 60) {
            $formattedDate =  TranslateTextHelper::translate($diffInMinutes . ' minutes ago');
        } elseif ($diffInHours < 24) {
            $formattedDate =  TranslateTextHelper::translate($diffInHours . ' hours ago');
        } else {
            $formattedDate = $feedbackDate->format('Y-m-d');
        }

        $comment = $existingFeedBack->comment ?? TranslateTextHelper::translate('No comment provided');

        $responseData = [
            'id' => $existingFeedBack->id ,
            'date' => $formattedDate ,
            'comment' => $comment ,
            'rate' => $existingFeedBack->rate ?? 500
        ];

        return response()->json($responseData,200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetLocationStatisticsRate($location_id): JsonResponse
    {

        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

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
            $ratingPercentages['rating_message'] = TranslateTextHelper::translate("Not rated yet");
            $ratingPercentages['value'] = 2;

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
            $ratingPercentages[$rating] = (double) number_format(($count / $totalRatings) * 100, 4);
        }

        $averageRating = (double) number_format($sumOfRatings / $totalRatings , 4);

        $ratingPercentages['total_ratings'] = $totalRatings;
        $ratingPercentages['average_rating'] = $averageRating;

        if ($averageRating > 3.2) {
            $ratingPercentages['rating_message'] = TranslateTextHelper::translate("Suitable");
            $ratingPercentages['value'] = 1;
        } else {
            $ratingPercentages['rating_message'] = TranslateTextHelper::translate("Discouraged");
            $ratingPercentages['value'] = 0;
        }

        return response()->json($ratingPercentages , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////

    public function GetFirstThreeFeedback(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all(), [
            "location_id" => 'required|integer|exists:locations,id',
            "rate" => 'required|integer'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $isRateNull = $request->rate === 0 ;

        $query = Feedback::with(['user.profile'])->where('Location_id' , $request->location_id);

        if(!$query->count() > 0)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate('There are no reviews for this place yet')
            ] , 422);
        }

        if($request->rate && !$isRateNull)
        {
            $query->where('rate' , $request->rate);
        }

        $feedbacks = $query->take(3)->get();

        if(!$feedbacks->count() > 0)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate("There are no $request->rate stars reviews for this place yet")
            ] , 422);
        }

        $transformedFeedbacks = [];

        foreach ($feedbacks as $feedback)
        {
            $feedbackDate = Carbon::parse($feedback->date);
            $now = Carbon::now();
            $diffInSeconds = $feedbackDate->diffInSeconds($now);
            $diffInMinutes = $feedbackDate->diffInMinutes($now);
            $diffInHours = $feedbackDate->diffInHours($now);
            $diffInDays = $feedbackDate->diffInDays($now);

            if ($diffInSeconds < 60) {
                $formattedDate = TranslateTextHelper::translate($diffInSeconds . ' seconds ago');
            } elseif ($diffInMinutes < 60) {
                $formattedDate = TranslateTextHelper::translate($diffInMinutes . ' minutes ago');
            } elseif ($diffInHours < 24) {
                $formattedDate = TranslateTextHelper::translate($diffInHours . ' hours ago');
            } else {
                $formattedDate = $feedbackDate->format('Y-m-d');
            }

            $transformedFeedbacks[] = [
                'id' => $feedback -> id ,
                'name' => $feedback -> user -> name ,
                'comment' => $feedback -> comment ?? TranslateTextHelper::translate('No comment provided') ,
                'rate' => $feedback -> rate ?? 500 ,
                'date' => $formattedDate,
                'profile_picture' => $feedback -> user -> profile -> profile_picture
            ];
        }

        return response()->json($transformedFeedbacks , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetAllFeedbacks($location_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $exist = Location::find($location_id);
        if(!$exist)
        {
            return response()->json(['error' => 'Location is not found !'], 404);
        }

        $feedbacks = Feedback::with(['user.profile'])->where('location_id' , $location_id)->get();

        if(!$feedbacks->count() > 0)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate('There are no reviews for this place yet')]
                , 422);
        }

        $response_result = [];
        foreach ($feedbacks as $feedback)
        {
            $feedbackDate = Carbon::parse($feedback->date);
            $now = Carbon::now();

            $diffInSeconds = $feedbackDate->diffInSeconds($now);
            $diffInMinutes = $feedbackDate->diffInMinutes($now);
            $diffInHours = $feedbackDate->diffInHours($now);
            $diffInDays = $feedbackDate->diffInDays($now);

            if($diffInSeconds < 60 ){
                $formattedDate = TranslateTextHelper::translate($diffInSeconds . ' seconds ago');
            } elseif ($diffInMinutes < 60){
                $formattedDate = TranslateTextHelper::translate($diffInMinutes. ' minutes ago');
            } elseif ($diffInHours < 24){
                $formattedDate = TranslateTextHelper::translate($diffInHours . ' hours ago');
            } else {
                $formattedDate = $feedbackDate->format('Y-m-d');
            }

            $response_result [] = [
                'id' => $feedback -> id ,
                'name' => $feedback -> user -> name ,
                'comment' => $feedback -> comment ?? TranslateTextHelper::translate('No comment provided') ,
                'rate' => $feedback -> rate ?? 500,
                'date' => $formattedDate ,
                'profile_picture' => $feedback -> user -> profile -> profile_picture ,
            ];
        }
        return response()->json($response_result , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function updateFeedback(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all() , [
            'location_id' => 'required|integer|exists:locations,id' ,
            'comment' => 'required_without:rate',
            'rate' => 'required_without:comment|numeric|between:0.5,5'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ] , 422);
        }

        $existingFeedback = Feedback::where('user_id' , $user['id'])->where('location_id' , $request->location_id)->first();

        if(!$existingFeedback)
        {
            return response()->json([
                'message' => 'No feedback found to update for this location !',
                'status_code' => 404
            ], 404);
        }

        $update_data = [];
        if($request->has('comment'))
        {
            $update_data['comment'] = $request->comment ;
        }
        if($request->has('rate'))
        {
            $update_data['rate'] = $request->rate ;
        }
        $update_data['date'] = now()->format('Y-m-d H:i:s');

        $existingFeedback->update($update_data);

        return response()->json([
            'message' => TranslateTextHelper::translate('Your feedback has been updated successfully , Thank you'),
            'status_code' => 200
        ] , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function deleteFeedback($location_id): JsonResponse
    {
        $user = Auth::user();

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $existingFeedback = Feedback::where('user_id', $user->id)->where('location_id' , $location_id)->first();

        if (!$existingFeedback) {
            return response()->json([
                "error" => "No feedback found for this user.",
                "status_code" => 404,
            ], 404);
        }

        $existingFeedback->forceDelete();

        return response()->json([
            "message" => TranslateTextHelper::translate("Your rating has been successfully deleted"),
            "status_code" => 200,
        ], 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetFeedBackByLocation ($location_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $location = Location::query()->find($location_id);
        if (!$location) {
            return response()->json([
                'message' => 'Location not found , Invalid location id'
            ], 404);
        }

        $feedbacks = $location->feedbacks()->with('user.profile')->get();

        if(!$feedbacks->count() > 0)
        {
            return response()->json([
                'message' => TranslateTextHelper::translate('There is no users feedback for this location yet')
            ] ,404);
        }

        $data = $feedbacks->pluck('date')->toArray();
        $data = TranslateTextHelper::batchTranslate($data);

        $response = [];

        foreach ($feedbacks as $feedback)
        {
            $response[] = [
                'feedback_id' => $feedback -> id,
                'name' => $feedback->user->name,
                'comment' => $feedback->comment ?? TranslateTextHelper::translate('No comment provided'),
                'rate' => $feedback->rate,
                'profile_picture' => $feedback->user->profile->profile_picture ,
                'date' => $data[$feedback->date]
            ];
        }
        return response()->json($response, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////

}

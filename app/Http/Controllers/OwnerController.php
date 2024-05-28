<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function WebGetOwners (): JsonResponse
    {
        $owners = User::with('profile')->where('role' , "Owner")->get();

        if(!$owners->count() > 0)
        {
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $response = [];
        foreach ($owners as $owner)
        {
            $response [] = [
                'owner_id' => $owner->id ,
                'name' => $owner->name ,
                'profile_picture' => $owner -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////
    public function WebDeleteFeedback($feedback_id): JsonResponse
    {
        $feedback = Feedback::query()->find($feedback_id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback not found',
                'status_code' => 404,
            ], 404);
        }

        $feedback->delete();

        return response()->json([
            'message' => 'Feedback has been deleted successfully !',
            'status_code' => 200,
        ], 200);

    }
}

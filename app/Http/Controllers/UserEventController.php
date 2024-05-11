<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserEventController extends Controller
{
//    public function createEvent(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'location_id' => 'required | exists:locations,id',
//            'date' => 'required | date',
//            'invitation_type' => 'required',
//            'description' => 'required',
//            'start_time' => 'required | date',
//            'end_time' => 'required | date',
//            'num_people_invited' => 'required | int',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                "error" => $validator->errors()->first(),
//                "status_code" => 422,
//            ], 422);
//        }
//    }
}

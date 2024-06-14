<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Feedback;
use App\Models\Food;
use App\Models\Location;
use App\Models\MainEvent;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function WebSearchAdmin (Request $request): JsonResponse
    {
        $validator = Validator::make( $request->all() , [
            'search' => 'required | string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $results = User::with('profile')
            ->where('role' , 'Admin')
            ->where('name' , 'LIKE' , '%' . $request->search . '%')
            ->orderBy('users.name')
            ->get();

        if(!$results->count() > 0)
        {
            return response()->json([
                "message" => "No result search found !" ,
                "status_code" => 404
            ] , 404);
        }

        $response = [];
        foreach ($results as $result)
        {
            $response [] = [
                'name' => $result->name ,
                'email' => $result->email ,
                'profile_picture' => $result -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebThreeStatistic(): JsonResponse
    {
        $feedbacks = Feedback::query()->count();

        $app_downloads = User::query()->where('role' , 'User')->whereNull('google_id')->count();

        $warehouses = Warehouse::query()->count();

        if(!$warehouses)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        $response = [
            ['id' => 1 , 'number' => $feedbacks , 'description' => 'Feedbacks counts'] ,
            ['id' => 2 , 'number' => $app_downloads , 'description' => 'Application downloads'] ,
            ['id' => 3 , 'number' => $warehouses , 'description' => 'Warehouse counts']
        ];
        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebCounts(): JsonResponse
    {
        $location = Location::query()->count();
        if(!$location){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Food = Food::query()->count();
        if(!$Food){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Drink = Drink::query()->count();
        if(!$Drink){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Accessory = Accessory::query()->count();
        if(!$Accessory){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $custom = 'Custom' ;

        $event = MainEvent::query()->where('name' , '!=' , $custom)->count();
        if(!$event){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $response = [
            ['number' => $location , 'description' => 'Location'] ,
            ['number' => $Food , 'description' => 'Food'] ,
            ['number' => $Drink , 'description' => 'Drink'] ,
            ['number' => $Accessory , 'description' => 'Accessory'] ,
            ['number' => $event , 'description' => 'Event']
        ];

        return response()->json($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function WebGetAdmins(): JsonResponse
    {
        $admins = User::with('profile')->where('role' , "Admin")->get();

        if(!$admins->count() > 0)
        {
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $response = [];
        foreach ($admins as $admin)
        {
            $response [] = [
                'owner_id' => $admin->id ,
                'name' => $admin->name ,
                'email' => $admin->email ,
                'profile_picture' => $admin -> profile -> profile_picture
            ];
        }

        return response()->json($response , 200);
    }
}


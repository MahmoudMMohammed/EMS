<?php

namespace App\Http\Controllers;

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
}

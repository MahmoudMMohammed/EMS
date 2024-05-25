<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function WebGetUserDownloadApp()
    {
        $users = User::with('profile')->where('role' , 'User')->get();

        if(!$users->count() > 0)
        {
            return response()->json([
                'message' => 'There are no users download the application yet' ,
                'states_code' => 404
            ] , 404);
        }

        $response = [];
        foreach ($users as $user)
        {
            $response [] = [
                'name' => $user -> name ,
                'email' => $user -> email ,
                'profile_picture' => $user -> profile -> profile_picture ,
                'registration_date' => $user -> updated_at -> format('Y-m-d')
            ];
        }
        return response()->json($response , 200);
    }
}

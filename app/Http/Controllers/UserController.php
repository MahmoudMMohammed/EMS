<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function WebGetUserDownloadApp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'required|string|in:all,today,week,month,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422,
            ], 422);
        }


        $filter = $request->input('filter');

        switch ($filter) {

            case 'today':
                $users = User::query()
                    ->where('role' , 'User')
                    ->whereDate('created_at', Carbon::today())
                    ->get();

                break;

            case 'week':
                $users = User::query()
                    ->where('role' , 'User')
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->get();

                break;

            case 'month':
                $users = User::query()
                    ->where('role' , 'User')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->get();

                break;

            case 'year':
                $users = User::query()
                    ->where('role' , 'User')
                    ->whereYear('created_at', Carbon::now()->year)
                    ->get();

                break;

            case 'all':
            default:
                $users = User::query()
                    ->where('role' , 'User')
                    ->get();

                if(!$users->count() > 0){
                    return response()->json([
                        "error" => "There are no users who have downloaded the application !",
                        "status_code" => 404,
                    ] , 404);
                }
                break;
        }

        if ($users->isEmpty() && $filter != 'all') {
            return response()->json([
                "message" => "There are no users who have downloaded the application at this time !",
                "status_code" => 404,
            ], 404);
        }

        $response = [];
        foreach ($users as $user)
        {
            $response [] = [
                'id' => $user->id ,
                'name' => $user->name ,
                'email' => $user->email ,
                'profile_picture' => $user -> profile -> profile_picture
            ];
        }

        return response()->json($response, 200);
    }
}

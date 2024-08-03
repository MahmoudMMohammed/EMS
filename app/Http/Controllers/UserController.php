<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Favorite;
use App\Models\Feedback;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function WebGetUserDownloadApp(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

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
                        "error" => TranslateTextHelper::translate("There are no users who have downloaded the application"),
                        "status_code" => 404,
                    ] , 404);
                }
                break;
        }

        if ($users->isEmpty() && $filter != 'all') {
            return response()->json([
                "message" => TranslateTextHelper::translate("There are no users who have downloaded the application at this time"),
                "status_code" => 404,
            ], 404);
        }

        $response = [];
        foreach ($users as $user)
        {
            $response [] = [
                'user_id' => $user->id ,
                'name' => $user->name ,
                'email' => $user->email ,
                'profile_picture' => $user -> profile -> profile_picture
            ];
        }

        return response()->json($response, 200);
    }

    /////////////////////////////////////////////////////////////////////////////////////
    public function GetUserProfileDetails($id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $exist = User::query()->find($id);
        if(!$exist)
        {
            return response()->json([
                'message' => 'User not found , Invalid user id' ,
                'status_code' => 404
            ], 404);

        }
        $users = User::where('role' , 'User')->where('id' , $id)->first();

        if(!$users)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $data = [ $users->role ,
            $users -> profile -> phone_number ?? 'there is no phone number' ,
            $users -> profile -> place_of_residence ?? 'There is no information about the place of residence' ,
            $users -> profile -> gender ?? 'Not set yet' ,
           ];

        $data = TranslateTextHelper::batchTranslateArray($data);

        $response = [
            'name' => $users->name ,
            'Registration' => $users->created_at->format('Y/m/d') ,
            'role' => $data[0] ,
            'email' => $users->email ,
            'phone_number' => $data[1] ,
            'residence' => $data[2] ,
            'birth_date' => $users -> profile -> birth_date ?? TranslateTextHelper::translate('Not set yet'),
            'gender' => $data[3] ,
            'about_me' => $users -> profile -> about_me ?? TranslateTextHelper::translate('Not set yet'),
            'profile_picture' => $users -> profile -> profile_picture
        ];

        return response() -> json($response , 200);
    }
    /////////////////////////////////////////////////////////////////////////////////////
    public function StatisticsUserExperience() :JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        $deleted_at = Feedback::query()->withTrashed()->whereNotNull('deleted_at')->count();

        $blocked = User::query()
            ->where('role' , '=' ,'User')
            ->where('is_blocked' , '=' , 1)
            ->count();

        $favorites = Favorite::query()->count();

        $response1 = [
            'number of deleted comments' => $deleted_at ,
            'color' => '#443391' ,
            'icon' => env('APP_URL') . '/Icon/3.png'
            ];

        $response2 = [
            'number of blocked user' => $blocked ,
            'color' => '#3E5EAB',
            'icon' => env('APP_URL') . '/Icon/1.png'
            ];
        $response3 = [
            'number of favorite item' => $favorites ,
            'color' => '#1495CF' ,
            'icon' => env('APP_URL') . '/Icon/2.png'
            ];

        $response = [$response1 , $response2 ,$response3];

        return response()->json($response , 200);
    }
    /////////////////////////////////////////////////////////////////////////////////////
    public function StatisticsMasculinity(): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        $user = User::query()->where('role' , 'User')->pluck('id')->toArray();

        $gender = Profile::query()
            ->whereIn('user_id' , $user)
            ->where('gender' , 'male')
            ->count();

        $total_users = count($user);

        if ($total_users == 0) {
            $malePercentage = 0;
        } else {
            $malePercentage = round(($gender / $total_users) * 100 , 2) ;
        }
        return response()->json(['Masculinity'=>$malePercentage] ,200);
    }
    /////////////////////////////////////////////////////////////////////////////////////
    public function StatisticsFemininity(): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        $user = User::query()->where('role' , 'User')->pluck('id')->toArray();

        $gender = Profile::query()
            ->whereIn('user_id' , $user)
            ->where('gender' , 'female')
            ->count();

        $total_users = count($user);

        if ($total_users == 0) {
            $femalePercentage = 0;
        } else {
            $femalePercentage = round(($gender / $total_users) * 100 ,2) ;
        }
        return response()->json(['Femininity'=>$femalePercentage] ,200);
    }
}

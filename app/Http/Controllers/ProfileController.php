<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateProfileGeneralInfo(Request $request)
    {
        $profile = $this->getUserProfile();

        $validator = Validator::make($request->all(),[
            'about_me' => 'nullable | string',
            'birth_date' => 'nullable | date_format:Y-m-d',
            'place_of_residence' => 'nullable | string',
            'gender' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $profile->about_me = $request->about_me;
        $profile->birth_date = $request->birth_date;
        $profile->place_of_residence = $request->place_of_residence;
        $profile->gender = $request->gender;
        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'status_code' => 200
        ],200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function getGeneralInfo()
    {
        $profile = $this->getUserProfile();

        return response()->json([
            'about_me'=> $profile->about_me,
            'birth_date' => $profile->birth_date,
            'place_of_residence' => $profile->place_of_residence,
            'gender' => $profile->gender,
            'status_code' => 200
        ],200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function updateProfilePicture(Request $request)
    {
        $profile = $this->getUserProfile();

        $validator = Validator::make($request->all(),[
            'profile_picture' => 'required | image'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        if($request->hasFile('profile_picture')){
            $image = $request->file('profile_picture');
            $destination = 'Profile Pictures/' . time() . ' user ' . $profile->user_id ;
            $image->move(public_path('Profile Pictures'),$destination);
        }else {
            // Handle the case when no image is uploaded
            return response()->json([
                'message' => 'No image uploaded.',
                'status_code' => 400
            ], 400);
        }

        $profile->profile_picture = $destination;
        $profile->save();
        return response()->json([
            'message' => 'Profile picture updated successfully',
            'status_code' => 200
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function getProfilePicture()
    {
        $profile = $this->getUserProfile();
        if(is_null($profile->profile_picture)){
            return response()->json([
                'message' => 'You have not added your picture yet!',
                'status_code' => 404
            ], 404);
        }
        return response()->json([
            'profile_picture' => $profile->profile_picture,
            'status_code' => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    //method to authenticate user and get its profile
    private function getUserProfile(){
        $user = Auth::user();

        $profile = Profile::whereUserId($user->id)->first();
        if (!$profile){
            return response()->json([
                'message' => 'Profile not found!',
                'status_code' => 404
            ],404);
        }
        return $profile;
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}

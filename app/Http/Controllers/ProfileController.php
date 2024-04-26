<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as password_rule;

class ProfileController extends Controller
{
    public function updateProfileGeneralInfo(Request $request): JsonResponse
    {
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

        $profile = $this->getUserProfile();

        $profile->about_me = $request->about_me;
        $profile->birth_date = $request->birth_date;
        $profile->place_of_residence = $request->place_of_residence;
        $profile->gender = $request->gender;
        $profile->save();

        return response()->json([
            'message' => 'General info updated successfully',
            'status_code' => 200
        ],200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function getGeneralInfo(): JsonResponse
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

    public function updateProfilePicture(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'profile_picture' => 'required | image'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $profile = $this->getUserProfile();

        if($request->hasFile('profile_picture')){
            $image = $request->file('profile_picture');
            $destination = 'Profile Pictures/' . time() . ' user ' . $profile->user_id ;
            $image->move(public_path('Profile Pictures'),$destination);
        }else {
            // Handle the case when no image is uploaded
            return response()->json([
                'error' => 'No image uploaded.',
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

    public function getProfilePicture(): JsonResponse
    {
        $profile = $this->getUserProfile();
        if(is_null($profile->profile_picture)){
            return response()->json([
                'error' => 'You have not added your picture yet!',
                'status_code' => 404
            ], 404);
        }
        return response()->json([
            'profile_picture' => $profile->profile_picture,
            'status_code' => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    public function updateProfilePrivacyInfo(Request $request): JsonResponse
    {
        $profile = $this->getUserProfile();

        $user = User::find($profile->user_id);

        $validator = Validator::make($request->all(),[
            "name" => 'required | min:2 | max:30 | regex:/^[A-Za-z\s]+$/ ',
            "email" => ['required', 'email' , Rule::unique('users')->ignore($user)],
            'phone_number' => ['nullable', 'regex:/^09[0-9]{8}$/', Rule::unique('profiles')->ignore($profile)],
            "password" => ['nullable' , password_rule::min(6)->numbers()->letters()->mixedCase() ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->has('password')){
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $profile->phone_number = $request->phone_number;
        $profile->save();

        return response()->json([
            'message' => 'Privacy updated successfully',
            'status_code' => 200
        ], 200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function getPrivacyInfo(): JsonResponse
    {
        $profile = $this->getUserProfile();

        $user = User::find($profile->user_id);

        return response()->json([
            'name'=> $user->name,
            'email' => $user->email,
            'phone_number' => $profile->phone_number,
            'status_code' => 200
        ],200);

    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function deleteAccount()
    {
        $user = Auth::user();

        $user->delete();

        return response()->json([
            'message' => 'Account deletion requested. You have 30 days to reactivate your account by logging in or it will be permanently deleted.',
            'status_code' => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    //method to get user with profile
    private function getUserProfile(){
        $user = Auth::user();

        $profile = Profile::whereUserId($user->id)->first();
        if (!$profile){
            return response()->json([
                'error' => 'Profile not found!',
                'status_code' => 404
            ],404);
        }
        return $profile;
    }
    ///////////////////////////////////////////////////////////////////////////////////////

}

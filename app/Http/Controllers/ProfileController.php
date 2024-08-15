<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
        $user =Auth::user();

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
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
            'message' => TranslateTextHelper::translate('General info updated successfully'),
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
            'profile_picture' => $profile->profile_picture,
            'status_code' => 200
        ],200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function updateProfilePicture(Request $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $this->getUserProfile();

        $validator = Validator::make($request->all(),[
            'profile_picture' => 'required | image'
        ]);
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }



        if($request->hasFile('profile_picture') && $user->role == "User"){
            $image = $request->file('profile_picture');
            $destination = 'ProfilePictures/Users/' . time() . ' ' . $user->role . ' ' . $profile->user_id ;
            $image->move(public_path('ProfilePictures/Users'),$destination);
        }

        elseif($request->hasFile('profile_picture') && $user->role != "User"){
            $image = $request->file('profile_picture');
            $destination = 'ProfilePictures/Owners&Admins/' . time() . ' ' . $user->role . ' ' . $profile->user_id ;
            $image->move(public_path('ProfilePictures/Owners&Admins'),$destination);
        }
        else {
            // Handle the case when no image is uploaded
            return response()->json([
                'error' => TranslateTextHelper::translate('No image uploaded.'),
                'status_code' => 400
            ], 400);
        }

        $profile->profile_picture = $destination;
        $profile->save();
        return response()->json([
            'message' => TranslateTextHelper::translate('Profile picture updated successfully'),
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
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
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
            'message' => TranslateTextHelper::translate('Privacy updated successfully'),
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

    public function deleteAccount(): JsonResponse
    {
        $user = Auth::user();

        $user->delete();

        return response()->json([
            'message' => TranslateTextHelper::translate('Account deletion requested. You have 30 days to reactivate your account by logging in or it will be permanently deleted.'),
            'status_code' => 200
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function updatePreferredLanguage(Request $request): JsonResponse
    {
        $profile = $this->getUserProfile();

        $validator = Validator::make($request->all(),[
            'preferred_language' => 'required | in:ar,en,zh,fr,de,ru,es,hi'
        ]);
        if ($validator->fails()) {
            TranslateTextHelper::setTarget($profile->preferred_language);
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }
        $profile->preferred_language = $request->preferred_language;

        if (!$profile->save()){
            return response()->json([
                'error' => TranslateTextHelper::translate('Could not update language please try again later!'),
                'status_code' => 400
            ], 400);
        }
        $profile->save();
        TranslateTextHelper::setTarget($profile->preferred_language);
        return response()->json([
            'message' => TranslateTextHelper::translate('Preferred language updated successfully'),
            'status_code' => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    public function updatePreferredCurrency(Request $request): JsonResponse
    {
        $profile = $this->getUserProfile();

        $validator = Validator::make($request->all(),[
            'preferred_currency' => 'required | in:SYP,USD,EUR,TRY,EGP,SAR,JOD,AED,LYD,KWD,GBP,QAR,BHD,SEK,CAD,OMR,NOK,DKK'
        ]);
        if ($validator->fails()) {
            TranslateTextHelper::setTarget($profile->preferred_language);
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }
        $profile->preferred_currency = $request->preferred_currency;

        if (!$profile->save()){
            return response()->json([
                'error' => TranslateTextHelper::translate('Could not update currency please try again later!'),
                'status_code' => 400
            ], 400);
        }
        $profile->save();
        TranslateTextHelper::setTarget($profile->preferred_language);
        return response()->json([
            'message' => TranslateTextHelper::translate('Preferred currency updated successfully'),
            'status_code' => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    //method to get user with profile
    private function getUserProfile()
    {
        $user = Auth::user();

        $profile = Profile::whereUserId($user->id)->first();
        if (!$profile){
            TranslateTextHelper::setTarget($profile->preferred_language);
            return response()->json([
                'error' => TranslateTextHelper::translate('Profile not found!'),
                'status_code' => 404
            ],404);
        }
        return $profile;
    }
    ///////////////////////////////////////////////////////////////////////////////////////

    public function WebGetCurrentAdmin(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $userdata = User::with('profile')->where('id', $user->id)->first();

        $response = [
            'user_id' => $userdata->id ,
            'user_name' => TranslateTextHelper::translate("Hi ". $userdata->name) ,
            'profile_picture' => $userdata -> profile -> profile_picture
        ];
        return response()->json($response , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function getAdminOwnerProfile():JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $profile = User::query()->where('id' , $user->id)->first();

        if(!$profile)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }


        $response = [
            'id' => $profile->id ,
            'name' => $profile->name ,
            'phone_number' => $profile->profile->phone_number ,
            'email' => $profile->email ,
            'birth_date' => $profile->profile->birth_date ,
            'picture' => $profile->profile->profile_picture,
            'role' => $profile->role,
            'verified' => 'verified' ,
            'Registration' => $profile->created_at->format('Y/m/d')
        ];

        return response()->json($response ,200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function editAdminOwnerProfile(Request $request):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'required|regex:/^[a-zA-Z\s]+$/' ,
            'email' => 'required|email|unique:users,email' ,
            'password' => ['required' , password_rule::min(6)->numbers()->letters()->mixedCase() ] ,
            'phone_number' => 'required|starts_with:09|digits:10',
            'birthday' => 'required|date_format:Y-m-d' ,
            'residence' => 'sometimes|nullable|string',
            'bio' => 'sometimes|nullable|max:100'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $admin = User::query()->where('id' , $user->id)->first();

        $adminProfile = Profile::query()->where('user_id' , $admin->id)->first();

        if(!$admin || !$adminProfile)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }


        $admin->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $adminProfile->update([
            'phone_number' => $request->input('phone_number'),
            'birth_date' => $request->input('birthday'),
            'about_me' => $request->input('residence'),
            'place_od_residence' => $request->input('bio'),
        ]);

        return response()->json([
            "message" => "accessory details updated successfully",
            "status_code" => 200,
        ], 200);

    }
}

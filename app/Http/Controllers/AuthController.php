<?php

namespace App\Http\Controllers;
use App\Events\NotificationEvent;
use App\Helpers\TranslateTextHelper;
use App\Mail\SendCodeResetPassword;
use App\Mail\SendEmailVerificationCode;
use App\Models\EmailVerification;
use App\Models\ResetCodePassword;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rules\Password as password_rule;
class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required | min:2 | max:30 | regex:/^[A-Za-z\s]+$/ ',
            "email" => 'required | email | unique:users,email',
            "password" => ['required' , 'confirmed' , password_rule::min(6)->numbers()->letters()->mixedCase() ] ,

        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }


        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        // Generate and store verification code
        $code = mt_rand(100000, 999999);
        $codeData = EmailVerification::create([
            'email' => $request->email,
            'code' => $code,
        ]);
        if($user){
            try {
                // Send email and check for success
                if (Mail::to($request['email'])->send(new SendEmailVerificationCode($codeData['code']))) {
                    // Email sent successfully
                    return response()->json([
                        "message" => "Registration succeeded, Please verify your email to continue",
                        'status_code' => 201
                    ], 201);
                } else {
                    // Email sending failed
                    return response()->json([
                        "message" => "Registration failed: Unable to send verification email.",
                        'status_code' => 400,
                    ], 400);
                }
            } catch (Exception $e) {
                // Handle any other exceptions during email sending
                return response()->json([
                    "message" => "Registration failed: An error occurred while sending verification email.",
                    'status_code' => 500,
                ], 500);
            }
        }else{
            return response()->json([
                "message" => "Registration failed!",
                'status_code' => 400,
            ], 400);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required | string | exists:email_verifications'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $emailVerification = EmailVerification::whereCode($request->code)->first();

        // Check if it's not expired: the time is 10 minutes
        if ($emailVerification->created_at < Carbon::now()->subMinutes(10)) {
            $emailVerification->delete();
            return response()->json([
                'message' => 'Verification code has expired!',
                'status_code'=>422,
                ], 422);
        }

        $user = User::whereEmail($emailVerification->email)->first();

        $token = JWTAuth::fromUser($user);

        if ($user){
            $user->verified = true;
            $user->save();
            return response()->json([
                'message' => 'Code verified',
                'token' => $token,
                'status_code' => 200
            ],200);
        }else{
            return response()->json([
                'message' => 'User not found!',
                'status_code' => 404,
            ],404);
        }

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required | email | exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'Invalid email or password.',
                'status_code' => 422
            ], 422);
        }


        $token = JWTAuth::attempt([
            'email' => $request->email ,
            'password' => $request->password
        ]);

        if(!$token){
            return response()->json([
                'message' => 'failed log in !',
                'status_code' => 400,
                ] , 400);
        }

        return response()->json([
            'token' => $token ,
            'status_code'=>200
        ] , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function logout(Request $request){
        try{
            if (Auth::user()){
                JWTAuth::invalidate(JWTAuth::getToken());
                return response()->json([
                    'message'=>'Logout successfully',
                    'status_code' => 200,
                ],200);
            }else{
                return response()->json([
                    'message'=>'Already logged out!',
                    'status_code' => 400,
                ],400);
            }

        }catch (JWTException $e){
            return response()->json([
                'message'=>'Logout failed!',
                'status_code' => 401,
            ],401);
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        //delete all old codes user sent before
        ResetCodePassword::where('email' , $request->email)->delete();

        //generate random code
        $request['code'] = mt_rand(100000,999999);

        //create a new code
        $codeData = ResetCodePassword::create([
            'email'=>$request->email,
            'code'=>$request['code'],
        ]);

        //send email to user
        Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));

        return response()->json([
            'message' => 'Code sent to your email',
            'status_code' => 200,
        ],200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function checkCode(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required|string|exists:reset_code_passwords'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        //find the code
        $passwordReset = ResetCodePassword::firstWhere('code' , $request->code);

        // check if it's not expired: the time is one hour
        if ($passwordReset->created_at < now()->subHour()){
            $passwordReset->delete();
            return response()->json([
                'message' => 'Password code has expired!',
                'status_code' => 422,
            ], 422);
        }

        return response()->json([
            'message' => 'Password code is valid',
            'status_code' => 200,
        ],200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => ['required' , 'confirmed' , password_rule::min(6)->numbers()->letters()->mixedCase() ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        //find the code
        $passwordReset = ResetCodePassword::firstWhere('code' , $request->code);

        // check if it's not expired: the time is one hour
        if ($passwordReset->created_at < now()->subHour()){
            $passwordReset->delete();
            return response()->json([
                'message' => 'Password code has expired!',
                'status_code' => 422,
                ], 422);
        }

        //find user's email
        $user = User::firstWhere('email' , $passwordReset->email);

        // check if the new password is the same as the old password
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'New password cannot be the same as the old!',
                'status_code' => 422,
                ], 422);
        }

        //update user password
        $request->password = Hash::make($request->password);
        $user->update([
            'password' => $request->password,
        ]);

        //delete current code
        $passwordReset->delete();

        return response()->json([
            'message' => 'password has been reset successfully',
            'status_code' => 200,
        ],200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function testNotifications($user_id)
    {
        $message = "Testing Pusher";

        event(new NotificationEvent($user_id, $message));

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function testTranslation()
    {
        // Set the source and target languages
        //TranslateTextHelper::setSource('en')->setTarget('ar');
        //TranslateTextHelper::setSource('en')->setTarget('es');
        //TranslateTextHelper::setSource('en')->setTarget('fr');

        // Translate the text
        $translatedText = TranslateTextHelper::translate('Hello, world!');

        // Output the translated text
        echo $translatedText;

    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
}

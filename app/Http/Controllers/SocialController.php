<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialController extends Controller
{
    //****************************  Facebook Login **********************************//
    //********** Web **********//
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {

            $user = Socialite::driver('facebook')->user();
            $findUser = User::where('facebook_id', $user->id)->first();

            if($findUser){
                Auth::login($findUser);
                return redirect()->intended();
            }else{
                $createUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'facebook_id' => $user->id,
                    'password' => bcrypt(Str::random(16)),
                ]);

                Auth::login($createUser);
                return redirect()->intended();
            }

        } catch (Exception $exception) {
            dd($exception->getMessage());
        }
    }
    //********** API **********//
    public function redirectToFacebookAPI()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookAPICallback()
    {
        try {
            // Obtain user details from Facebook
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            // Check if the user with this email already exists in your database
            $user = User::where('email', $facebookUser->getEmail())->first();

            if (!$user) {
                // If the user doesn't exist, create a new user
                $user = User::create([
                    'name' => $facebookUser->getName(),
                    'email' => $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                ]);
            }
            $token = JWTAuth::fromUser($user);
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            // Handle exceptions, log errors, or return an error response
            return response()->json(['error' => 'Failed to authenticate with Facebook.']);
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    //****************************  Google Login **********************************//

    //******** Web ********//
    // Redirect to Google for authentication
    public function redirectToGoogleWeb()
    {
        return Socialite::driver('google')->redirect();
    }
    ///////////////////////////////////////////////////////////////

    // Handle the callback from Google
    public function handleGoogleWebCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $findUser = User::where('google_id', $user->id)->first();

            if ($findUser) {
                Auth::login($findUser);
                return redirect()->intended();
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'password' => bcrypt(Str::random(16)),
                ]);

                Auth::login($newUser);
                return redirect()->intended();
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    ///////////////////////////////////////////////////////////////

    //******** API ********//
    public function redirectToGoogleAPI()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    ///////////////////////////////////////////////////////////////

    public function handleGoogleAPICallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Google login failed'], 401);
        }

        $authenticatedUser = $this->findOrCreateUser($user);

        $token = JWTAuth::fromUser($authenticatedUser);

        //return response()->json(['token' => $token]);
        return redirect()->route('redirectedUser', ['user_id' => $authenticatedUser->id]);
    }
    ///////////////////////////////////////////////////////////////

    public function getRedirectedUser(Request $request)
    {
        $user = User::find($request->query('user_id'));

        $token = JWTAuth::fromUser($user);

        return response()->json(["token" => $token],200);
    }
    ///////////////////////////////////////////////////////////////
    private function findOrCreateUser($googleUser)
    {
        //Implement your logic to find or create a user in your database based on the Google user data.
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        return $user;
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
}

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//register new user
Route::post('/register', [AuthController::class, 'register']);

//verify user email to continue to the app
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

//login existing user
Route::post('/login', [AuthController::class, 'login']);



//requesting code through email to change password
Route::post('/password/email',  [AuthController::class , 'forgotPassword']);

//checking code validity
Route::post('/password/code/check', [AuthController::class , 'checkCode']);

//resetting user password
Route::post('/password/reset', [AuthController::class , 'resetPassword']);



//redirecting to google for authentication
Route::get('/auth/google', [SocialController::class, 'redirectToGoogleAPI']);

//handling google callback with user info
Route::get('/auth/google/callback' ,[SocialController::class, 'handleGoogleAPICallback']);


Route::middleware([UserMiddleware::class])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);


});

Route::middleware([AdminMiddleware::class])->group(function () {

});

Route::middleware([OwnerMiddleware::class])->group(function () {

});

//test
//Route::get('/notification/user/{user_id}' ,[AuthController::class, 'testNotifications']);
//Route::get('/translate' ,[AuthController::class, 'testTranslation']);

////test

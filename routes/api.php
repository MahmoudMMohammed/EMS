<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::post('/password/email',  [AuthController::class , 'forgotPassword']);
Route::post('/password/code/check', [AuthController::class , 'checkCode']);
Route::post('/password/reset', [AuthController::class , 'resetPassword']);

Route::get('/auth/google', [SocialController::class, 'redirectToGoogleAPI']);
Route::get('/auth/google/callback' ,[SocialController::class, 'handleGoogleAPICallback']);

//test
//Route::get('/notification/user/{user_id}' ,[AuthController::class, 'testNotifications']);
//Route::get('/translate' ,[AuthController::class, 'testTranslation']);

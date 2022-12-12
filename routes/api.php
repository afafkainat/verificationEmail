<?php

use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ApiNewPasswordController;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/signup',[UserController::class,'signup']);
Route::post('/update-profile',[UserController::class,'update_profile']);
Route::get('/validateRegister',[UserController::class,'validateRegister']);
Route::post('/login',[UserController::class,'login']);

Route::post('/forget-password',[PasswordResetController::class,'forgetPassword']);

// Route::get('/validateRegister',function(){
// Mail::send(new VerifyEmail());
// return view('welcome');
// });

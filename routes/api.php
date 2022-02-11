<?php

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
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
Route::group(['prefix' => 'v1'], function () {

    //API route for register new user
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
    //API route for login user
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/choose_level', [App\Http\Controllers\API\AuthController::class, 'choose_level']);
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);

    //API route for users
    Route::resource('users', App\Http\Controllers\API\UserController::class);

});

//Protecting Routes
Route::group(['prefix' => 'v1','middleware' => ['auth:sanctum']], function () {
    // API route for logout user
    // //Test LDAP
    // Route::get('/profile', function(Request $request) {
    //     $user=DB::table('users')->paginate(3);
    //     // $user=DB::table('users')->first();
    //     return ResponseFormatter::success($user,null);

    // });
});

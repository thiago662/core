<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\EnterpriseController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\UserController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
            
    Route::post('signup', [AuthController::class, 'signup'])->name('signup');

    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('refresh', [AuthController::class, 'refresh'])->name('refresh');

    // Middleware de autendificação com jwt
    Route::group(['middleware' => ['jwt.auth']], function () {
    
        // Enterprises = index, store, show, update, destroy;
        // middleware = index, store, show, update, destroy;
        Route::name('enterprises.')->group(function () {

            Route::apiResource('enterprises', EnterpriseController::class)->except([
                'store'
            ]);
    
        });
    
        // Users = index, store, show, update, destroy;
        // middleware = index, store, destroy;
        Route::name('users.')->group(function () {

            Route::apiResource('users', UserController::class);
    
        });
    
        // Leads = index, store, show, update, destroy;
        // middleware = index;
        Route::name('leads.')->group(function () {

            Route::apiResource('leads', LeadController::class);
    
        });
    
        // FolllowUps = store, show;
        // middleware = ;
        Route::name('followUps.')->group(function () {

            Route::apiResource('followUps', FollowUpController::class)->only([
                'store','show'
            ]);
    
        });

    });

});

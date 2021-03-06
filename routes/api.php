<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnterpriseController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\UserController;
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

            // exclui o usuario e move seus lide
            Route::post('move/{id}', [UserController::class, 'deleteMove'])->name('users.move');

            // Filtro
            Route::post('users/filter', [UserController::class, 'filter'])->name('users.filter');

            // Todos menos atendente
            Route::get('clerks', [UserController::class, 'clerks'])->name('users.clerks');

            // Retornar seu perfil
            Route::get('profile', [UserController::class, 'profile'])->name('users.profile');
        });

        // Leads = index, store, show, update, destroy;
        // middleware = index;
        Route::name('leads.')->group(function () {

            Route::apiResource('leads', LeadController::class);

            // Filtro
            Route::post('leads/filter', [LeadController::class, 'filter'])->name('leads.filter');
        });

        // FolllowUps = store, show, update;
        // middleware = ;
        Route::name('followups.')->group(function () {

            Route::apiResource('followups', FollowUpController::class)->only([
                'store', 'show', 'update'
            ]);

            // show by id
            Route::get('followup/{id}', [FollowUpController::class, 'showId'])->name('followup.show');
        });

        // Dashboard = graphicLead, graphicOpen, graphicClose, graphicSale, ranking, leadsTotal, leadsOpen, leadsClose, leadsSales;
        // middleware = ;
        Route::name('dashboard.')->group(function () {

            Route::post('dashboard/graphic/lead', [DashboardController::class, 'graphicLead'])->name('dashboard.graphic.lead');

            Route::post('dashboard/graphic/open', [DashboardController::class, 'graphicOpen'])->name('dashboard.graphic.open');

            Route::post('dashboard/graphic/close', [DashboardController::class, 'graphicClose'])->name('dashboard.graphic.close');

            Route::post('dashboard/graphic/sale', [DashboardController::class, 'graphicSale'])->name('dashboard.graphic.sale');

            Route::post('dashboard/total', [DashboardController::class, 'leadsTotal'])->name('dashboard.total');

            Route::post('dashboard/open', [DashboardController::class, 'leadsOpen'])->name('dashboard.open');

            Route::post('dashboard/close', [DashboardController::class, 'leadsClose'])->name('dashboard.close');

            Route::post('dashboard/sales', [DashboardController::class, 'leadsSales'])->name('dashboard.sales');

            Route::get('dashboard/ranking', [DashboardController::class, 'rankingLead'])->name('dashboard.ranking.user');

            Route::get('dashboard/ranking/source', [DashboardController::class, 'rankingSource'])->name('dashboard.ranking.source');
        });

        // FolllowUps = index, destroy, update, show,;
        // middleware = store;
        Route::name('log.')->group(function () {

            Route::apiResource('log/leads', LogController::class)->except([
                'store'
            ]);

            Route::delete('log/leads', [LogController::class, 'destroyAll'])->name('log.leads.destroy.all');
        });
    });
});

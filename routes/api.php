<?php

use App\Http\Controllers\CICDController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LostGameController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\TGFileController;
use App\Http\Controllers\UserController;
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


/* Public API */

Route::get('/games', [PublicApiController::class, 'games']);
Route::get('/games/{slug}', [PublicApiController::class, 'game']);
Route::get('/items', [PublicApiController::class, 'items']);
Route::get('/items/{slug}', [PublicApiController::class, 'item']);
Route::get('/users', [PublicApiController::class, 'users']);
Route::get('/lost-games', [PublicApiController::class, 'lostGames']);
Route::get('/nofuzy/1', [PublicApiController::class, 'nofuzy1']); // Temporary endpoint
Route::get('/nofuzy/2', [PublicApiController::class, 'nofuzy2']); // Temporary endpoint


/* CI/CD API */

Route::get('/make-backup', [CICDController::class, 'makeBackup']);
Route::get('/before-ia', [CICDController::class, 'beforeIa']);
Route::post('/set-ia', [CICDController::class, 'setIa']);

/* Admin API */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('tgfiles', TGFileController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('games', GameController::class);
    Route::get('lost-games', [LostGameController::class, 'index']);
    Route::post('lost-games', [LostGameController::class, 'store']);
    Route::post('lost-games/rename', [LostGameController::class, 'rename']);
    Route::delete('lost-games/{filename}', [LostGameController::class, 'destroy']);
});

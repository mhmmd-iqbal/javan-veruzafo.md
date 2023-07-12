<?php

use App\Http\Controllers\Api\DesaController;
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

Route::get('desa/', [DesaController::class, 'index']);
Route::get('desa/{id}', [DesaController::class, 'show']);
Route::post('desa/', [DesaController::class, 'create']);
Route::put('desa/{id}', [DesaController::class, 'update']);
Route::delete('desa/{id}', [DesaController::class, 'destroy']);


Route::fallback(function(){
    return response()->json(['message' => 'Page Not Found'], 404);
});


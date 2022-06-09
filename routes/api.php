<?php

use App\Models\User;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('users', \App\Http\Controllers\UserController::class);
Route::apiResource('transactions', \App\Http\Controllers\TransactionController::class);
Route::get('current-balance', '\App\Http\Controllers\UserController@currentBalance');
Route::get('export-transactions', '\App\Http\Controllers\TransactionController@exportTransactions');

Route::get('exampleendpoint', function() {
    $transaction = \App\Models\Transaction::inRandomOrder()->doesnthave('transactions')->toSql();
    dd($transaction);
});

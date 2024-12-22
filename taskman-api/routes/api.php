<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// T A S K

Route::post('/task', [TaskController::class, 'store']);
Route::get('/task-list', [TaskController::class, 'index']);
Route::get('show-task-details/{id}', [TaskController::class, "show"]);
Route::post('/update-task/{id}', [TaskController::class, 'update']);
Route::post('/delete-task/{id}', [TaskController::class, 'destroy']);
Route::post('/search-task', [TaskController::class, 'search']);

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([], function ($router) {
    Route::post('login', [AuthController::class,'login']);
    Route::post('register', [AuthController::class,'register']);
});

Route::middleware(['auth:api'])->group(function(){
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('me', [AuthController::class,'me']);
    Route::post('refresh', [AuthController::class,'refresh']);

    // T A S K

    Route::post('/task', [TaskController::class, 'store'])->middleware('can:create tasks');
    Route::get('/task-list', [TaskController::class, 'index'])->middleware('can:access tasks');
    Route::get('show-task-details/{id}', [TaskController::class, "show"])->middleware('can:access tasks');
    Route::post('/update-task/{id}', [TaskController::class, 'update'])->middleware('can:update tasks');
    Route::post('/delete-task/{id}', [TaskController::class, 'destroy'])->middleware('can:delete tasks');
    Route::post('/search-task', [TaskController::class, 'search'])->middleware('can:access tasks');
    Route::post('/find-filtered-tasks', [TaskController::class, 'filteredTasks'])->middleware('can:access tasks');
    Route::post('/year-wise-tasks', [TaskController::class, 'yearWiseTasks'])->middleware('can:access dashboard');
    Route::post('/month-year-completed-tasks', [TaskController::class, 'monthYearCompletedTasks'])->middleware('can:access dashboard');

    // Route::post('/task', [TaskController::class, 'store']);
    // Route::get('/task-list', [TaskController::class, 'index']);
    // Route::get('show-task-details/{id}', [TaskController::class, "show"]);
    // Route::post('/update-task/{id}', [TaskController::class, 'update']);
    // Route::post('/delete-task/{id}', [TaskController::class, 'destroy']);
    // Route::post('/search-task', [TaskController::class, 'search']);
    // Route::post('/find-filtered-tasks', [TaskController::class, 'filteredTasks']);
    // Route::post('/year-wise-tasks', [TaskController::class, 'yearWiseTasks']);
    // Route::post('/month-year-completed-tasks', [TaskController::class, 'monthYearCompletedTasks']);

});




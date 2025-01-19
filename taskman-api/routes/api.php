<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignedTaskController;

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
    Route::post('/year-wise-tasks', [TaskController::class, 'yearWiseTasks'])->middleware('can:access admin dashboard');
    Route::post('/month-year-completed-tasks', [TaskController::class, 'monthYearCompletedTasks'])->middleware('can:access admin dashboard');

    // E M P L O Y E E

    Route::post('/employee', [EmployeeController::class, 'store'])->middleware('can:create employee');
    Route::get('/employee-list', [EmployeeController::class, 'index'])->middleware('can:access employees');
    Route::get('/show-employee-details/{id}', [EmployeeController::class, 'show'])->middleware('can:access employees');
    Route::post('/update-employee/{id}', [EmployeeController::class, 'update'])->middleware('can:update employee');
    Route::post('/delete-employee/{id}', [EmployeeController::class, 'delete'])->middleware('can:delete employee');
    Route::post('/search-employee', [EmployeeController::class, 'search'])->middleware('can:access employees');

    // U S E R

    Route::post('/update-user/{id}', [UserController::class, 'update']);

    // ASSIGN TASKS

    Route::post('/assign-task', [AssignedTaskController::class, 'assignTask'])->middleware('can:assign task');
    Route::get('/all-assigned-tasks', [AssignedTaskController::class, 'allAssignedTasks'])->middleware('can:assign task'); //only for admin
    Route::get('/employee-assigned-tasks', [AssignedTaskController::class, 'employeeAssignedTasks'])->middleware('can:access assigned tasks');
    Route::post('/employee-assignedtask-count', [AssignedTaskController::class, 'employeeWiseAssignedTaskCount'])->middleware('can:access employee dashboard');
    Route::get('/complete-incomplete-task-ratio', [AssignedTaskController::class, 'completeIncompleteTaskRatio'])->middleware('can:access employee dashboard');
    Route::get('/two-months-productivity', [AssignedTaskController::class, 'compareTwoMonthsProductivity'])->middleware('can:access employee dashboard');
    Route::get('/employee-wise-assigned-tasks-list', [AssignedTaskController::class, 'employeeWiseAssignedTasksList'])->middleware('can:access assigned tasks');
    Route::get('/assigned-task-details/{id}', [AssignedTaskController::class, 'assignedTaskDetails'])->middleware('can:access assigned tasks');
});




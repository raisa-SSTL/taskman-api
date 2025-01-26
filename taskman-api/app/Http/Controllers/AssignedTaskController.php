<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AssignedTaskController extends Controller
{
    //
    public function assignTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        // Ensure the logged-in admin can only assign tasks they created
        $task = Task::where('id', $request->task_id)
            ->where('user_id', auth()->id()) // Verify the task belongs to the logged-in admin
            ->first();

        if (!$task) {
            return response()->json(['message' => 'You can only assign tasks that you have created.'], 403);
        }

        // Check if the task status is Complete
        if ($task->status === 'Complete') {
            return response()->json(['message' => 'This task is marked as Complete and cannot be assigned to any employee.'], 403);
        }

        // Ensure the logged-in admin can only assign tasks to employees they created
        $employee = Employee::where('id', $request->employee_id)
            ->where('admin_id', auth()->id()) // Verify the employee was created by the logged-in admin
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'You can only assign tasks to employees you have created.'], 403);
        }

        // Ensure the task is not already assigned to another employee
        $existingAssignment = AssignedTask::where('task_id', $request->task_id)->first();
        if ($existingAssignment) {
            return response()->json(['message' => 'This task has already been assigned to an employee.'], 409);
        }

        // Create the assigned task record
        $assignedTask = AssignedTask::create([
            'task_id' => $request->task_id,
            'employee_id' => $request->employee_id,
            'assigned_by' => auth()->id(), // Current logged-in admin
        ]);

        return response()->json(['message' => 'Task assigned successfully', 'assignedTask' => $assignedTask]);
    }

    public function allAssignedTasks()
    {
        $assignedTasks = AssignedTask::where('assigned_by', auth()->id())
                                    ->with([
                                        'task',
                                        'employee:id,name,user_id'
                                    ])
                                    ->get();

        if ($assignedTasks->isEmpty()){
            \Log::info('No assigned tasks found for user:', ['id' => auth()->id()]);
            return response()->json([
                'message' => "You have not assigned any tasks yet",
                'data' => []
            ], 200);
        }

        \Log::info('Assigned tasks retrieved:', ['tasks' => $assignedTasks]);
        return response()->json([
            'message' => 'Assigned Tasks retrieved successfully',
            'data' => $assignedTasks
        ], 200);
    }

    public function employeeAssignedTasks() //List of tasks assigned to logged in employee
    {
        try {
            // Check if the logged-in user has the 'employee' role
            if (!Auth::user()->hasRole('employee')) {
                return response()->json([
                    'message' => 'Unauthorized access. This action is only allowed for employees.',
                ], 403);
            }

            // Fetch tasks assigned to the logged-in employee
            $employee = Employee::where('user_id', auth()->id())->first();
            if (!$employee) {
                return response()->json([
                    'message' => 'Unauthorized access. Employee not found.',
                ], 403);
            }

            $employeeTasks = AssignedTask::where('employee_id', $employee->id)
                ->with(['task'])
                ->get();

            if ($employeeTasks->isEmpty()) {
                return response()->json([
                    'message' => 'Currently you have no assigned tasks',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'message' => 'List of assigned tasks retrieved successfully',
                'data' => $employeeTasks
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in employeeAssignedTasks:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    // public function employeeWiseAssignedTaskCount(Request $request)
    // {
    //     // Validate the request to ensure the year is provided
    //     $request->validate([
    //         'year' => 'required|integer',
    //     ]);

    //     $year = $request->year;

    //     try {
    //         // Step 1: Check if the authenticated user is an employee and get their employee ID
    //         $authEmployee = Employee::where('user_id', auth()->id())->first();

    //         if (!$authEmployee) {
    //             return response()->json(['message' => 'Authenticated user is not an employee.'], 404);
    //         }

    //         // Step 2: Check if the employee has tasks assigned in the assigned_tasks table
    //         $authEmployeeTask = AssignedTask::where('employee_id', $authEmployee->id)->first();

    //         if (!$authEmployeeTask) {
    //             return response()->json(['message' => 'No tasks assigned to the authenticated employee.'], 404);
    //         }

    //         // Step 3: Find the admin ID who assigned tasks to this employee
    //         $adminId = $authEmployeeTask->assigned_by;

    //         // Step 4: Get all employees assigned tasks by this admin
    //         $employees = Employee::whereHas('assignedTasks', function ($query) use ($adminId) {
    //             $query->where('assigned_by', $adminId);
    //         })->with(['assignedTasks.task'])->get();

    //         // Step 5: Prepare the result with completed task counts for the given year
    //         $result = $employees->map(function ($employee) use ($year) {
    //             $completedTaskCount = $employee->assignedTasks->filter(function ($assignedTask) use ($year) {
    //                 // Safeguard against null task or end_date
    //                 return $assignedTask->task &&
    //                     $assignedTask->task->status === 'Complete' &&
    //                     $assignedTask->task->end_date &&
    //                     $assignedTask->task->end_date->year == $year;
    //             })->count();

    //             return [
    //                 'employee_name' => $employee->name,
    //                 'completed_task_count' => $completedTaskCount,
    //             ];
    //         });

    //         // Step 6: Check if all completed task counts are 0
    //         $hasCompletedTasks = $result->pluck('completed_task_count')->sum() > 0;

    //         if (!$hasCompletedTasks) {
    //             return response()->json([
    //                 'message' => 'No tasks were completed this year',
    //                 'data' => [],
    //             ]);
    //         }

    //         // Step 7: Return the result
    //         return response()->json([
    //             'message' => 'data retrieved successfully',
    //             'data' => $result
    //         ]);

    //     } catch (\Exception $e) {
    //         // Log the error for debugging
    //         Log::error('Error in employeeWiseAssignedTaskCount: ' . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         // Return a generic error response
    //         return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
    //     }
    // }

    public function employeeWiseAssignedTaskCount(Request $request)
    {
        // Validate the request to ensure the year is provided
        $request->validate([
            'year' => 'required|integer',
        ]);

        $year = $request->year;

        try {
            // Check if the authenticated user is an employee
            $authEmployee = Employee::where('user_id', auth()->id())->first();

            if (!$authEmployee) {
                return response()->json(['message' => 'Authenticated user is not an employee.', 'data' => []], 404);
            }

            // Check if the employee has tasks assigned
            $authEmployeeTask = AssignedTask::where('employee_id', $authEmployee->id)->first();

            if (!$authEmployeeTask) {
                return response()->json(['message' => 'No tasks assigned to the authenticated employee.', 'data' => []], 200);
            }

            // Get admin ID who assigned tasks
            $adminId = $authEmployeeTask->assigned_by;

            // Get employees assigned tasks by this admin
            $employees = Employee::whereHas('assignedTasks', function ($query) use ($adminId) {
                $query->where('assigned_by', $adminId);
            })->with(['assignedTasks.task'])->get();

            // Prepare result with completed task counts
            $result = $employees->map(function ($employee) use ($year) {
                $completedTaskCount = $employee->assignedTasks->filter(function ($assignedTask) use ($year) {
                    return $assignedTask->task &&
                        $assignedTask->task->status === 'Complete' &&
                        $assignedTask->task->end_date &&
                        $assignedTask->task->end_date->year == $year;
                })->count();

                return [
                    'employee_name' => $employee->name,
                    'completed_task_count' => $completedTaskCount,
                ];
            });

            // If no employees or no completed tasks, return an empty dataset
            if ($result->isEmpty() || $result->pluck('completed_task_count')->sum() === 0) {
                return response()->json(['message' => 'No tasks were completed this year.', 'data' => []], 200);
            }

            // Return data
            return response()->json([
                'message' => 'Data retrieved successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error in employeeWiseAssignedTaskCount: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function completeIncompleteTaskRatio()
    {
        // Step 1: Match auth()->id() with user_id in the employees table
        $employee = Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        // Step 2: Find all assigned tasks for this employee
        $assignedTasks = AssignedTask::where('employee_id', $employee->id)->get();

        if ($assignedTasks->isEmpty()) {
            return response()->json([
                'message' => 'This employee has no assigned tasks',
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'totalAssignedTasks' => 0,
                'completedTasks' => 0,
                'incompleteTasks' => 0,
            ]);
        }

        // Step 3: Calculate the total assigned tasks
        $totalAssignedTasks = $assignedTasks->count();

        // Step 4: Calculate the number of completed tasks
        $completedTasks = Task::whereIn('id', $assignedTasks->pluck('task_id'))
            ->where('status', 'Complete')
            ->count();

        // Step 5: Calculate the number of incomplete tasks
        $incompleteTasks = $totalAssignedTasks - $completedTasks;

        // Step 6: Return the response
        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'totalAssignedTasks' => $totalAssignedTasks,
            'completedTasks' => $completedTasks,
            'incompleteTasks' => $incompleteTasks,
        ]);
    }

    public function compareTwoMonthsProductivity()
    {
        // Step 1: Match auth()->id() with user_id in the employees table
        $employee = Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        // Step 2: Get current month and previous month date ranges
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Step 3: Get month names
        $currentMonthName = Carbon::now()->format('F'); // e.g., "January"
        $previousMonthName = Carbon::now()->subMonth()->format('F');

        // Step 4: Fetch assigned tasks for the logged-in employee
        $assignedTasks = AssignedTask::where('employee_id', $employee->id)->pluck('task_id');

        // Step 5: Count tasks completed in the previous month
        $previous_month_completed_tasks = Task::whereIn('id', $assignedTasks)
            ->where('end_date', '>=', $previousMonthStart)
            ->where('end_date', '<=', $previousMonthEnd)
            ->count();

        // Step 6: Count tasks completed in the current month
        $current_month_completed_tasks = Task::whereIn('id', $assignedTasks)
            ->where('end_date', '>=', $currentMonthStart)
            ->where('end_date', '<=', $currentMonthEnd)
            ->count();

        // Step 7: Calculate percentage change
        if ($previous_month_completed_tasks > 0) {
            $percentage_change = (($current_month_completed_tasks - $previous_month_completed_tasks) / $previous_month_completed_tasks) * 100;
        } else {
            $percentage_change = $current_month_completed_tasks > 0 ? 100 : 0; // Handle division by zero
        }

        // Step 8: Return the response
        return response()->json([
            'employee_id' => $employee->id,
            'name' => $employee->name,
            'previous_month' => $previousMonthName,
            'current_month' => $currentMonthName,
            'previous_month_completed_tasks' => $previous_month_completed_tasks,
            'current_month_completed_tasks' => $current_month_completed_tasks,
            'percentage_change' => round($percentage_change, 2), // Round to 2 decimal places
        ]);
    }

    public function employeeWiseAssignedTasksList()
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if(!$employee){
            return response()->json([
                'message' => 'Employee not found'
            ], 404);
        }

        $assignedTasks = AssignedTask::where('employee_id', $employee->id)
                        ->with('task')
                        ->get();

        if($assignedTasks->isEmpty()){
            return response()->json([
                'message' => 'No task was assigned to this employee'
            ], 200);
        }

        // Use sortBy to sort tasks by ascending deadline
        $sortedTasks = $assignedTasks->sortBy(function ($assignedTask) {
            return optional($assignedTask->task)->deadline; // Handle null deadlines
        });

        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'assigned_task_list' => $sortedTasks->map(function ($assignedTask) {
                return [
                    'id' => $assignedTask->id,
                    'task_id' => $assignedTask->task_id,
                    'assigned_at' => $assignedTask->created_at,
                    'task_details' => $assignedTask->task, // Includes task details
                ];
            })->values() // Reset keys after sorting
        ], 200);
    }

    //----
    public function employeeWiseAssignedTaskList2(Request $request, $id)
    {
        $employee = Employee::where('id', $id)->first();
        if(!$employee){
            return response()->json([
                'message' => 'Employee not found'
            ], 404);
        }
        $assignedTasks = AssignedTask::where('employee_id', $employee->id)
                        ->with('task')
                        ->get();
        if($assignedTasks->isEmpty()){
            return response()->json([
                'message' => 'No task was assigned to this employee'
            ], 200);
        }
        $sortedTasks = $assignedTasks->sortBy(function ($assignedTask) {
            return optional($assignedTask->task)->deadline; // Handle null deadlines
        });
        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'assigned_task_list' => $sortedTasks->map(function ($assignedTask) {
                return [
                    'id' => $assignedTask->id,
                    'task_id' => $assignedTask->task_id,
                    'assigned_at' => $assignedTask->created_at,
                    'task_details' => $assignedTask->task, // Includes task details
                ];
            })->values() // Reset keys after sorting
        ], 200);
    }
    //----

    public function assignedTaskDetails(Request $request, $id)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if(!$employee){
            return response()->json([
                'message' => 'Employee not found'
            ], 404);
        }
        // Check if there is an assigned task for the authenticated employee
        $assignedTask = AssignedTask::with('task')
                        ->where('employee_id', $employee->id)
                        ->where('task_id', $id)
                        ->first();

        if (!$assignedTask) {
            return response()->json(['message' => 'No assigned task found for this employee or task ID'], 404);
        }

        // Return task details
        return response()->json([
            'assignedTask' => $assignedTask
        ], 200);
    }

    public function updateAssignedTask(Request $request, $id)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if(!$employee){
            return response()->json([
                'message' => 'Employee not found'
            ], 404);
        }
        // Check if there is an assigned task for the authenticated employee
        $assignedTask = AssignedTask::with('task')
                        ->where('employee_id', $employee->id)
                        ->where('task_id', $id)
                        ->first();

        if (!$assignedTask) {
            return response()->json([
                'message' => 'No assigned task found for this employee or task ID',
            ], 404);
        }

         // Validate the request data
        $validatedData = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string',
        ]);

        // Update the task fields
        $task = $assignedTask->task;
        $task->update($validatedData);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 200);
    }
}

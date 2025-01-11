<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

    public function employeeAssignedTasks()
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


}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\Employee;

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

}

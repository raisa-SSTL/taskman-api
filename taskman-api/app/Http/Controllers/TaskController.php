<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\AssignedTask;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $user = auth()->user();

            // Retrieve all tasks, sorted by creation date (latest first)
            // $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(5);
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->get();

            // Return a successful JSON response with the tasks
            return response()->json([
                'success' => true,
                'message' => 'Tasks retrieved successfully.',
                'data' => $tasks,
            ], 200);
        } catch (\Exception $e) {
            // Handle errors and return an appropriate error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving tasks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Retrieve the currently logged-in user
        $user = auth()->user();

        // Create a new task
        // $task = Task::create($validatedData);
        // Create a new task and associate it with the user
        $task = $user->tasks()->create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Task created successfully!',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = auth()->user();

        // Attempt to find the task by ID
        // $task = Task::find($id);
        $task = Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

        // Check if the task exists
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found/ You do not have permission to view this task.'
            ], 404);
        }

        // Return the task details
        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $user = auth()->user();

        // Find the task by ID
        // $task = Task::find($id);
        $task = Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

        // Check if the task exists
        if (!$task) {
            return response()->json([
                'message' => 'Task not found or you do not have permission to update this task.',
            ], 404);
        }

        // Update the task with validated data
        $task->update($validatedData);

        // Return a success response
        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $user = auth()->user();
            // Find the task by ID
            // $task = Task::findOrFail($id);
            $task = Task::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

            if (!$task) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Task not found or you do not have permission to delete this task.',
                        ], 404);
            }

            // Delete the related assigned task entry if it exists
            AssignedTask::where('task_id', $task->id)->delete();

            // Delete the task
            $task->delete();

            // Return a success response
            return response()->json([
                'message' => 'Task and its assigned task (if any) deleted successfully!',
            ], 200);
        }
        catch (\Exception $e) {
            // Return a generic error response for other exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the task.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request)
    {
        // Validate the input to ensure 'title' is provided
        $request->validate([
            'title' => 'required|string',
        ]);

        // Retrieve the search query
        $searchTitle = $request->input('title');

        $user = auth()->user();

        // Perform the search using a case-insensitive LIKE query
        $tasks = Task::where('title', 'LIKE', '%' . $searchTitle . '%')
                        ->where('user_id', $user->id)
                        ->get();

        // Return the results as a JSON response
        return response()->json([
            'success' => true,
            'data' => $tasks,
            'message' => count($tasks) > 0 ? 'Tasks retrieved successfully.' : 'No tasks found matching the search query.',
        ]);
    }

    public function filteredTasks(Request $request)
    {
        // Validate the incoming filter data
        $validated = $request->validate([
            'priority' => 'array|nullable', // Expecting an array of priorities
            'status' => 'array|nullable',  // Expecting an array of statuses
        ]);

        // Retrieve filters from the request
        $priorityFilter = $validated['priority'] ?? [];
        $statusFilter = $validated['status'] ?? [];

        $user = auth()->user();

        // Build the query dynamically
        // $query = Task::query();
        $query = Task::where('user_id', $user->id);

        // Apply priority filter if provided
        if (!empty($priorityFilter)) {
            $query->whereIn('priority', $priorityFilter);
        }

        // Apply status filter if provided
        if (!empty($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        // Execute the query to get filtered tasks
        // $tasks = $query->get();
        $query->orderBy('created_at', 'desc');
        $tasks = $query->paginate(5);

        // Return the filtered tasks as a JSON response
        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function yearWiseTasks(Request $request)
    {
        // Validate the year input
        $request->validate([
            'year' => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        // Get the year from the request
        $year = $request->input('year');

        $user = auth()->user();

        // Filter tasks created in the given year
        $tasks = Task::whereYear('created_at', $year)
                        ->where('user_id', $user->id)
                        ->get();

        // if ($tasks->isEmpty()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No tasks found for the given year.',
        //     ], 404);
        // }

        // Return the filtered tasks
        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ], 200);
    }

    public function monthYearCompletedTasks(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12', // Month as integer (1-12)
            'year' => 'required|integer|min:1900',      // Year as integer (>=1900)
        ]);

        // Extract month and year from the request
        $month = $request->input('month');
        $year = $request->input('year');

        $user = auth()->user();

        // Query tasks with status "Complete" and filter by month and year of end_date, selecting specific fields
        $tasks = Task::select('id', 'title', 'priority', 'status', 'end_date')
            ->where('status', 'Complete')
            ->whereMonth('end_date', $month)
            ->whereYear('end_date', $year)
            ->where('user_id', $user->id)
            ->orderBy('end_date', 'desc')
            ->paginate(5);

        // if($tasks->isEmpty()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No tasks found for the given month and year.',
        //     ], 404);
        // }

        // Return the filtered tasks as a JSON response
        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }



}

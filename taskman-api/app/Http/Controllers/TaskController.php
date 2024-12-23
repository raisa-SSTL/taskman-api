<?php

namespace App\Http\Controllers;
use App\Models\Task;

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
            // Retrieve all tasks, sorted by creation date (latest first)
            $tasks = Task::orderBy('created_at', 'desc')->paginate(5);

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

        // Create a new task
        $task = Task::create($validatedData);

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
        // Attempt to find the task by ID
        $task = Task::find($id);

        // Check if the task exists
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found.'
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Find the task by ID
        $task = Task::find($id);

        // Check if the task exists
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
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
            // Find the task by ID
            $task = Task::findOrFail($id);

            // Delete the task
            $task->delete();

            // Return a success response
            return response()->json([
                'message' => 'Task deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Return a not found response if the task doesn't exist
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        } catch (Exception $e) {
            // Return a generic error response for other exceptions
            return response()->json([
                'message' => 'An error occurred while deleting the task.',
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

        // Perform the search using a case-insensitive LIKE query
        $tasks = Task::where('title', 'LIKE', '%' . $searchTitle . '%')->get();

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

        // Build the query dynamically
        $query = Task::query();

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


}

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
            $tasks = Task::orderBy('created_at', 'desc')->get();

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

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

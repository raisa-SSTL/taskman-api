<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:15',
        ]);

        try {
            // Start a database transaction
            DB::beginTransaction();

            Log::info('Starting transaction for employee creation.');

            // Create the employee user
            $employeeUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign the "employee" role
            $employeeUser->assignRole('employee');

            Log::info('User created successfully with ID: ' . $employeeUser->id);

            // Create the employee record in the employees table
            $employee = Employee::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                // 'password' => Hash::make($validated['password']), // Can be omitted if not needed in employees table
                'phone' => $validated['phone'],
                'admin_id' => auth()->id(), // Associate with the logged-in admin
                'user_id' => $employeeUser->id,
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'user' => $employeeUser,
                'employee' => $employee,
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            // Log the error for debugging
            Log::error('Employee creation failed: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'message' => 'Failed to create employee. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $user = auth()->user();
            $employees = $user->employees()->orderBy('created_at', 'desc')->paginate(5);

            return response()->json([
                'success' => true,
                'message' => 'Employees retrieved successfully',
                'data' => $employees
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving employees.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

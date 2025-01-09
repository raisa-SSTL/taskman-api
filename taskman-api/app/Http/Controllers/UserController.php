<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function update(Request $request, $id)
    {
        Log::info('Attempting to update user with ID: ' . $id);
        if ($id != auth()->id()) {
            Log::warning('Unauthorized update attempt by user ID: ' . auth()->id());
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own account.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:15',
        ]);

        try {
            DB::beginTransaction();

            Log::info('Transaction started for user update.');

            // Find the user by ID
            $user = User::find($id);
            if (!$user) {
                Log::warning('User not found with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Update the user record
            $user->update([
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
            ]);

            Log::info('User updated successfully.', ['user_id' => $user->id]);

            // Check if the user is also an employee
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                // Update the employee record
                $employee->update([
                    'name' => $validated['name'] ?? $employee->name,
                    'email' => $validated['email'] ?? $employee->email,
                    'phone' => $validated['phone'] ?? $employee->phone,
                ]);

                Log::info('Employee updated successfully.', ['employee_id' => $employee->id]);
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User and related employee updated successfully.',
                'user' => $user,
                'employee' => $employee,
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            Log::error('Failed to update user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


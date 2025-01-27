<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password'])
        ]);
        // Assign the 'admin' role to the newly created user
        $user->assignRole('admin');

        $token = auth('api')->login($user);
        return $this->respondWithToken($token);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();

        // return $this->respondWithToken($token);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // role name
                'permissions' => $user->getAllPermissions()->pluck('name'), // Array of permission names
            ],
        ]);
    }

    // public function me()
    // {
    //     $user = auth()->user();
    //     // Check if the user is an employee and fetch the associated employee record
    //     $employee = Employee::where('user_id', auth()->id())->first();

    //     return response()->json([
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'roles' => $user->getRoleNames()->first(), // Array of role names
    //             'phone' => $employee ? $employee->phone : null,
    //             'permissions' => $user->getAllPermissions()->pluck('name'), // Array of permission names
    //         ],
    //     ]);
    // }

    public function me()
    {
        try {
            $user = auth()->user();

            // Initialize phone as null
            $phone = null;
            $employeeId = null;

            $role = $user->getRoleNames()->first();

            // Check if the logged-in user has the role 'employee'
            if ($role === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $phone = $employee->phone;
                    $employeeId = $employee->id;
                } else {
                    return response()->json([
                        'message' => 'Employee record not found for the user.',
                    ], 404);
                }
            }

            // Return user details, including phone if the employee exists
            return response()->json([
                'user' => [
                    'user_id' => $user->id,
                    'employee_id' => $employeeId,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->first(), // Array of role names
                    'phone' => $phone,
                    'permissions' => $user->getAllPermissions()->pluck('name'), // Array of permission names
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in me method: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching user information. Please try again.',
            ], 500);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}

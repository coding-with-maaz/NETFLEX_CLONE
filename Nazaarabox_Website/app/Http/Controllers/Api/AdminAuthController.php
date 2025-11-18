<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $username = $request->username;
            $password = $request->password;

            // Find admin by email or name
            $admin = Admin::where(function($query) use ($username) {
                $query->where('email', $username)
                      ->orWhere('name', $username);
            })->first();

            if (!$admin) {
                // If no admin exists, create default admin
                if (Admin::count() === 0) {
                    Log::info('Creating default admin user');
                    // Use create but ensure password is hashed properly
                    $hashedPassword = Hash::make('Admin123!@#');
                    $admin = Admin::create([
                        'name' => 'admin',
                        'email' => 'admin@nazaarabox.com',
                        'password' => $hashedPassword, // Pass already hashed password
                        'role' => 'super_admin',
                        'is_active' => true,
                    ]);
                    Log::info('Default admin created', ['admin_id' => $admin->id]);
                } else {
                    Log::warning('Admin login failed - user not found', [
                        'username' => $username,
                        'total_admins' => Admin::count()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid credentials'
                    ], 401);
                }
            }

            // Check if admin is active
            if (!$admin->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated'
                ], 403);
            }

            // Verify password
            // Since Admin model might have password cast, get raw attribute
            $storedPassword = $admin->getAttributes()['password'] ?? $admin->password;
            
            // Debug logging (remove in production)
            Log::info('Admin login attempt', [
                'username' => $username,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'password_length' => strlen($password),
                'stored_password_length' => strlen($storedPassword),
                'stored_password_start' => substr($storedPassword, 0, 10),
            ]);
            
            if (!Hash::check($password, $storedPassword)) {
                Log::warning('Admin login failed - password mismatch', [
                    'username' => $username,
                    'admin_id' => $admin->id,
                    'password_match' => false
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials. Please check your username and password.'
                ], 401);
            }

        // Create a simple token/session identifier
        // For simplicity, we'll use a basic token stored in database
        // In production, you might want to use Laravel Sanctum or JWT
        $token = bin2hex(random_bytes(32));
        
        // Store token in admin session (you might want to create an admin_sessions table)
        // For now, we'll just return success with admin data
        // The frontend will store this in localStorage

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'role' => $admin->role,
                    ],
                    'tokens' => [
                        'access_token' => $token,
                        'refresh_token' => $token, // In production, implement proper refresh tokens
                        'token_type' => 'Bearer',
                        'expires_in' => 3600 * 24, // 24 hours
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        // In production, invalidate the token here
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get admin profile
     */
    public function profile(Request $request)
    {
        try {
            // Get token from Authorization header or request
            $token = $request->bearerToken() ?? $request->get('token');
            
            // Try to get admin from localStorage data if available
            // In production, validate the token against database
            $adminData = $request->get('admin_data');
            
            if ($adminData) {
                $admin = Admin::find($adminData['id']);
            } else {
                // For now, return the first admin or create a default response
                $admin = Admin::first();
            }
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'role' => $admin->role,
                        'is_active' => $admin->is_active,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching profile: ' . $e->getMessage()
            ], 500);
        }
    }
}


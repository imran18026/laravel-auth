<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    // Middleware is now defined in the routes file
    public function __construct()
    {
        // No middleware needed here as it's defined in routes/api.php
    }

    /**
     * Admin dashboard stats
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'admins' => User::whereHas('roles', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->count()
            ]
        ]);
    }

    /**
     * List users based on role permissions
     */
    public function userList(): JsonResponse
    {
        $users = User::with('roles')
            ->when(!auth()->user()->isSuperAdmin(), function ($query) {
                $query->whereHas('roles', fn($q) => $q->where('slug', '!=', 'super-admin'));
            })
            ->get();

        return response()->json($users->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'roles' => $user->roles->pluck('name'),
            'is_admin' => $user->isAdmin(),
            'is_super_admin' => $user->isSuperAdmin()
        ]));
    }

    /**
     * Super admin only endpoint
     */
    public function superAdminOnly(): JsonResponse
    {
        return response()->json([
            'message' => 'This endpoint is accessible only by super admins',
            'secret_data' => 'Confidential information'
        ]);
    }
}

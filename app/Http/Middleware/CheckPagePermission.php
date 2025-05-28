<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    private $publicRoutes = [
        'dashboard',
        'profile.edit',
        'profile.update',
        'logout',
        'password.confirm',
        'password.update'
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        // Check if user is a super admin
        if (in_array($user->email, config('superadmins.emails', []))) {
            return $next($request);
        }

        // Get the current route name
        $currentRoute = $request->route()->getName();
        if (!$currentRoute || in_array($currentRoute, $this->publicRoutes)) {
            return $next($request);
        }

        // Read permissions from JSON file
        $permissions = $this->getUserPermissions($user->email);

        // If user has no permissions for this route
        if (empty($permissions) || !in_array($currentRoute, $permissions)) {
            // Find the user's super admin(s) who can grant permissions
            $superAdmins = implode(', ', config('superadmins.emails', []));
            return redirect()->route('dashboard')
                ->with('message', 'You need permission to access this page. Please contact one of the administrators: ' . $superAdmins);
        }

        return $next($request);
    }

    private function getUserPermissions($email)
    {
        if (!Storage::exists('user_permissions.json')) {
            $this->initializePermissionsFile();
            return [];
        }

        $permissions = json_decode(Storage::get('user_permissions.json'), true);
        return $permissions[$email]['permissions'] ?? [];
    }

    private function initializePermissionsFile()
    {
        $defaultPermissions = [
            'test@test.com' => ['permissions' => [], 'is_super_admin' => true],
            'shar@shar.com' => ['permissions' => [], 'is_super_admin' => true],
            'hp2025.final@gmail.com' => ['permissions' => [], 'is_super_admin' => true]
        ];
        Storage::put('user_permissions.json', json_encode($defaultPermissions, JSON_PRETTY_PRINT));
    }
}

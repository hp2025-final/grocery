<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        // Get the current route name
        $routeName = $request->route()->getName();
        if (!$routeName) {
            return $next($request);
        }

        // Read permissions from JSON file
        $permissions = $this->getUserPermissions($user->email);

        // If user has no permissions for this route
        if (empty($permissions) || !in_array($routeName, $permissions)) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Please contact administrator.');
        }

        return $next($request);
    }

    private function getUserPermissions(string $email): array
    {
        if (!Storage::exists('user_permissions.json')) {
            Storage::put('user_permissions.json', json_encode([]));
            return [];
        }

        $permissions = json_decode(Storage::get('user_permissions.json'), true) ?? [];
        return $permissions[$email]['permissions'] ?? [];
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * Redirects authenticated users based on their role.
     * Only admin role has access to the system currently.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin users proceed to admin dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Non-admin roles - no portal available yet
        // Log them out and show appropriate message
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'error',
            'Portal untuk role Anda belum tersedia. Saat ini hanya Admin yang dapat mengakses sistem.'
        );
    }
}

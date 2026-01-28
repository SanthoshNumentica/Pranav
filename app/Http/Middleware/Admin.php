<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->role == 'admin') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this page.'
            ], 403);
        }

        session()->flash('error', 'You do not have any permission to access this page');

        // If the user has a role, redirect to that role's route, otherwise redirect /
        $redirectRoute = $request->user() ? $request->user()->role : 'login';

        try {
            return redirect()->route($redirectRoute);
        } catch (\Exception $e) {
            return redirect('/');
        }
    }
}

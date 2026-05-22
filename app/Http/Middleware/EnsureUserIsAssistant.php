<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAssistant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!in_array(auth()->user()->role_id, [1, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Assistant access required.',
            ], 403);
        }

        return $next($request);
    }
}

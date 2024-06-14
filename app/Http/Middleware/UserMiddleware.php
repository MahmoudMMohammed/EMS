<?php

namespace App\Http\Middleware;

use App\Helpers\TranslateTextHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        // Check if user's role is allowed
        $allowedRoles = ['User', 'Admin', 'Owner'];
        if ($user && in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        return response()->json([
            'error' => TranslateTextHelper::translate('Unauthorized, You are not User to access this page!')
        ], 403);
    }

}

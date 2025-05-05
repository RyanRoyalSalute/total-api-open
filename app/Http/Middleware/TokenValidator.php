<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $mobilePhone = $request->header('mobile');
        $userId = $request->header('userId');
        $authorizationHeader = $request->header('Authorization');

        // Validate the Bearer token
        if (empty($authorizationHeader) || empty($userId) || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return apiResponse(401, null, 'Unauthorized. Missing token, userId or mobile.', 401);
        }

        if (empty($mobilePhone)) {
            return apiResponse(4001, null, 'Mobile phone is required.', 400);
        }

        if (empty($userId)) {
            return apiResponse(4001, null, 'UserId is required.', 400);
        }

        $bearerToken = str_replace('Bearer ', '', $authorizationHeader);

        // Check if the mobile and token match a user in the database
        $user = DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->where('token', $bearerToken)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return apiResponse(401, null, 'Unauthorized. Invalid credentials.', 401);
        }

        // Proceed to the next middleware
        return $next($request);
    }
}

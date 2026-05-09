<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneAccessSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('phone_access_user_id')) {
            return redirect()->route('login')->with('status', __('Enter your phone number to view your investments.'));
        }

        return $next($request);
    }
}

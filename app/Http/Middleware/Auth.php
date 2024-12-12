<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
class Auth
{
    use GeneralTrait;

    public function handle(Request $request, Closure $next, $guard)
    {
        if(\Illuminate\Support\Facades\Auth::guard($guard)->check())
            return $next($request);
        return $this->returnError('please sign in first', 400);
    }
}

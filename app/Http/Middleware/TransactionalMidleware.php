<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionalMidleware
{
    //before
    public function before($request, $controller, $method){
        DB::beginTransaction();
    }

    //after
    public function after($request, $controller, $method, $response)
    {
        DB::commit();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $this->before();
        $next($request);
        $this->after();
    }
}

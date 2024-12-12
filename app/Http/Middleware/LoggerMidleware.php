<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoggerMidleware
{
    //before
    public function before($request, $controller, $method){
        $file = fopen('storage/logs/requests.log', "a");
        $txt = "\n" . $request . "\n" ;
        fwrite($file, $txt);
        fclose($file);
    }

    //after
    public function after($request, $controller, $method, $response)
    {
        $file = fopen("storage/logs/responses.log", "a") ;
        $txt = "\n" . $response . "\n" ;
        fwrite($file, $txt);
        fclose($file);
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

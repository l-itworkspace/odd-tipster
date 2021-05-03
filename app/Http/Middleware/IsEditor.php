<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsEditor
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

        if(auth()->user()->is_editor){
            return $next($request);
        }

        return redirect()->back();
    }
}

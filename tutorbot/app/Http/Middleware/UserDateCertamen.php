<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserDateCertamen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check()){
            $ultimo_res_certamen = auth()->user()>evaluaciones()->orderBy('created_at', 'desc')->first();

            if(isset($ultimo_res_certamen) && $ultimo_res_certamen->finalizado == false){
                $certamen = $ultimo_res_certamen->certamen;
                $_now = Carbon::now();
                if($_now->gte(Carbon::parse($certamen->fecha_termino))){
                    $ultimo_res_certamen->finalizado = true;
                    $ultimo_res_certamen->save();
                }
            }
        }
        return $next($request);
    }
}

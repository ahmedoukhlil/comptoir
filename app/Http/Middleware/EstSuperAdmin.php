<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EstSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()?->estSuperAdmin()) {
            abort(403);
        }

        // Le back-office Syslog n'est pas traduit : toujours en français,
        // indépendamment de la locale choisie par l'utilisateur pour les
        // écrans agent/propriétaire.
        App::setLocale('fr');

        return $next($request);
    }
}

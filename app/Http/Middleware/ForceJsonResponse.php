<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
  /**
   * Forzar que las peticiones sean tratadas como JSON (evita redirecciones en validación).
   */
  public function handle(Request $request, Closure $next)
  {
    // Si no hay Accept, o no contiene application/json, lo establecemos
    $accept = $request->headers->get('Accept');
    if (empty($accept) || strpos($accept, 'application/json') === false) {
      $request->headers->set('Accept', 'application/json');
    }

    // También indicar que es una petición AJAX
    if (!$request->ajax()) {
      $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    }

    return $next($request);
  }
}

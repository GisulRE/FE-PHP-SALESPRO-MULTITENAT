<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BlockedModule
{
  /**
   * Handle an incoming request.
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @param string $moduleSlug  Slug used in blocked_modules array (e.g. 'adjustment-account')
   */
  public function handle($request, Closure $next, $moduleSlug)
  {
    $user = Auth::user();
    if ($user) {
      $role = DB::table('roles')->where('id', $user->role_id)->first();
      if ($role && isset($role->blocked_modules) && $role->blocked_modules) {
        $blocked = json_decode($role->blocked_modules, true) ?: [];
        if (in_array($moduleSlug, $blocked)) {
          // Abort with 403 or redirect
          return abort(403, 'Module blocked: ' . $moduleSlug);
        }
      }
    }
    return $next($request);
  }
}

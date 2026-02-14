<?php

namespace App\Http\Middleware;

use DB;
use Auth;
use Closure;

class Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*if (Auth::check() && Auth::user()->isActive()) {
            $sync = Session::get('sincronizacion_siat');
            $token = Session::get('token_siat');
            if (($sync == null || $sync == false) && $token != null) {
                Log::info("Verifica Sincronizacion SIAT");
                $siatPanelController = new \App\Http\Controllers\SiatPanelController();
                $registrosSiat = $siatPanelController->getRegistrosSiat(0, 0);
                foreach ($registrosSiat['registros'] as $key => $registro) {
                    $updated_at = date('Y-m-d', strtotime($registro->updated_at));
                    $diff = strtotime($updated_at) - strtotime(date('Y-m-d'));
                    if ($diff < 0) {
                        Log::info("Iniciando Sincronizacion SIAT...");
                        $requestSiat = new Request();
                        $requestSiat->setMethod('PUT');
                        $requestSiat->request->add(['registro_id' => $registro->id]);
                        $requestSiat->request->add(['operacion' => $registro->operacion]);
                        $requestSiat->request->add(['sucursal' => $registrosSiat['sucursal']]);
                        $requestSiat->request->add(['codigo_punto_venta' => $registrosSiat['punto_venta']]);
                        $requestSiat->request->add(['nit' => $registrosSiat['nit'][0]]);
                        $requestSiat->request->add(['auth' => true]);
                        $result = $siatPanelController->update($requestSiat, 1);
                        if ($result['status'] == true) {
                            Log::info("Finalizo Sincronizacion SIAT");
                            Session::put('sincronizacion_siat', true);
                        } else {
                            Log::error("Fallo Sincronizacion SIAT");
                            Session::put('sincronizacion_siat', false);
                        }
                    }
                }
            }
            return $next($request);
        }

        return redirect('/dashboard');*/


        if (Auth::check() && Auth::user()->isActive()) {
            $permissions = DB::table('permissions')
                ->select('name')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_id', Auth::user()->role_id)
                ->get();

            $permisos = json_decode(json_encode($permissions), true);
            $p_ready = [];
            foreach ($permisos as $p) {
                $p_ready[] = $p['name'];
            }

            session()->put('permissions', $p_ready);

            return $next($request);
        }

        return redirect('/dashboard');

    }
}
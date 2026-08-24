<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Transfer;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ViewServiceProvider extends ServiceProvider
{
  public function boot()
  {
    View::composer(['layout.partials.navbar', 'layout.main'], function ($view) {
      static $cachedData = null;

      if ($cachedData !== null) {
        $view->with($cachedData);
        return;
      }

      $pendingTransfersCount = 0;
      $pendingTransfers = collect();

      if (Auth::check()) {
        try {
          $user = Auth::user();
          $permissions = is_array(session('permissions')) ? session('permissions') : [];
          $canAcceptTransfers = ($user->role_id <= 2) || in_array('accept-transfers', $permissions);

          if ($canAcceptTransfers) {
            if ($user->role_id <= 2) {
              $pendingTransfers = \App\Transfer::where('status', 2)->latest()->take(10)->get();
            } else {
              $warehouseId = optional($user->biller)->warehouse_id;
              if ($warehouseId) {
                $pendingTransfers = \App\Transfer::where('status', 2)
                  ->where('to_warehouse_id', $warehouseId)
                  ->latest()
                  ->take(10)
                  ->get();
              }
            }
            $pendingTransfersCount = $pendingTransfers->count();
          }
        } catch (\Throwable $e) {
          // safe fallback
        }
      }

      $cachedData = [
        'pendingTransfersCount' => $pendingTransfersCount,
        'pendingTransfers' => $pendingTransfers,
      ];

      $view->with($cachedData);
    });
  }
}

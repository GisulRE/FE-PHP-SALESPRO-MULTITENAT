<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Transfer;
use Spatie\Permission\Models\Role;

class ViewServiceProvider extends ServiceProvider
{
  public function boot()
  {
    View::composer('*', function ($view) {
      $pendingTransfersCount = 0;
      $pendingTransfers = collect();

      if (Auth::check()) {
        $user = Auth::user();
        $role = Role::find($user->role_id);

        if ($role && $role->hasPermissionTo('accept-transfers')) {

          if ($user->role_id <= 2) {
            $pendingTransfers = Transfer::where('status', 2)->get();
          } else {
            $warehouseId = optional($user->biller)->warehouse_id;

            if ($warehouseId) {
              $pendingTransfers = Transfer::where('status', 2)
                ->where('to_warehouse_id', $warehouseId)
                ->get();
            }
          }

          $pendingTransfersCount = $pendingTransfers->count();
        }
      }

      $view->with([
        'pendingTransfersCount' => $pendingTransfersCount,
        'pendingTransfers' => $pendingTransfers,
      ]);
    });
  }
}

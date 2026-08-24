<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Clase helper para verificar permisos sin usar Spatie PermissionRegistrar.
 *
 * Spatie carga TODOS los permisos como modelos Eloquent en memoria (~142 MB)
 * cada vez que se llama hasPermissionTo(). Esta clase reemplaza ese comportamiento
 * con una consulta DB directa y un cache estatico por request (~5 KB).
 */
class RolePermission
{
    private static ?array $permissions = null;
    private static ?int $loadedRoleId = null;

    public static function load(): void
    {
        $roleId = Auth::check() ? (int) Auth::user()->role_id : null;
        if ($roleId === null) {
            self::$permissions = [];
            return;
        }
        if (self::$permissions !== null && self::$loadedRoleId === $roleId) {
            return;
        }
        self::$loadedRoleId = $roleId;
        self::$permissions = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $roleId)
            ->pluck('permissions.name')
            ->toArray();
    }

    public static function check(string $permission): bool
    {
        self::load();
        return in_array($permission, self::$permissions ?? [], true);
    }

    public static function all(): array
    {
        self::load();
        $perms = self::$permissions ?? [];
        return empty($perms) ? ['dummy text'] : $perms;
    }

    public static function reset(): void
    {
        self::$permissions = null;
        self::$loadedRoleId = null;
    }
}
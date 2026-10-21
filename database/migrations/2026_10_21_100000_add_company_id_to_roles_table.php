<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('roles', 'company_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });

            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('roles', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                    });
                } catch (\Exception $e) {
                    // Ignore FK error if index/keys mismatch
                }
            }
        }

        // 1. Assign company_id from migration_map for imported roles (e.g. company 2)
        if (Schema::hasTable('migration_map')) {
            $mappedRoles = DB::table('migration_map')
                ->where('table_name', 'roles')
                ->get();

            foreach ($mappedRoles as $map) {
                DB::table('roles')
                    ->where('id', $map->new_id)
                    ->update(['company_id' => $map->company_id]);
            }
        }

        // 2. Assign company_id = 1 for original roles
        DB::table('roles')
            ->whereIn('id', [1, 2, 4, 5, 6, 7])
            ->update(['company_id' => 1]);

        // 3. Deactivate orphaned duplicate roles (ids 8 to 65)
        DB::table('roles')
            ->whereBetween('id', [8, 65])
            ->update(['is_active' => 0]);

        // Clean up redundant permission mappings for inactive orphaned roles to save space
        DB::table('role_has_permissions')
            ->whereIn('role_id', range(8, 65))
            ->delete();

        DB::table('roles')
            ->whereBetween('id', [8, 65])
            ->delete();

        // 4. Backfill any roles referenced by existing users
        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->whereNotNull('company_id')
                ->whereNotNull('role_id')
                ->get();

            foreach ($users as $user) {
                DB::table('roles')
                    ->where('id', $user->role_id)
                    ->whereNull('company_id')
                    ->update(['company_id' => $user->company_id]);
            }
        }

        // 5. Ensure company 3 has its own roles if it exists
        $company3 = DB::table('companies')->where('id', 3)->first();
        if ($company3) {
            $hasCompany3Roles = DB::table('roles')->where('company_id', 3)->exists();
            if (!$hasCompany3Roles) {
                $baseRoles = DB::table('roles')->where('company_id', 1)->where('is_active', 1)->get();
                $newAdminRoleId = null;

                foreach ($baseRoles as $baseRole) {
                    $newRoleId = DB::table('roles')->insertGetId([
                        'name' => $baseRole->name,
                        'description' => $baseRole->description,
                        'is_active' => $baseRole->is_active,
                        'guard_name' => $baseRole->guard_name ?? 'web',
                        'blocked_modules' => $baseRole->blocked_modules,
                        'company_id' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (strtolower($baseRole->name) === 'administrador' && !$newAdminRoleId) {
                        $newAdminRoleId = $newRoleId;
                    }

                    // Copy permissions from base role
                    $permissions = DB::table('role_has_permissions')->where('role_id', $baseRole->id)->get();
                    $insertPerms = [];
                    foreach ($permissions as $p) {
                        $insertPerms[] = [
                            'permission_id' => $p->permission_id,
                            'role_id' => $newRoleId,
                        ];
                    }
                    if (!empty($insertPerms)) {
                        foreach (array_chunk($insertPerms, 500) as $chunk) {
                            DB::table('role_has_permissions')->insert($chunk);
                        }
                    }
                }

                // Update users of company 3 to the new admin role
                if ($newAdminRoleId) {
                    DB::table('users')
                        ->where('company_id', 3)
                        ->where('role_id', 1)
                        ->update(['role_id' => $newAdminRoleId]);

                    DB::table('model_has_roles')
                        ->where('role_id', 1)
                        ->where('model_id', 7)
                        ->where('model_type', 'App\\User')
                        ->update(['role_id' => $newAdminRoleId]);
                }
            }
        }

        // 6. Default fallback for any remaining roles without company
        DB::table('roles')
            ->whereNull('company_id')
            ->update(['company_id' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('roles', 'company_id')) {
            Schema::table('roles', function (Blueprint $table) {
                try {
                    $table->dropForeign(['company_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
                $table->dropColumn('company_id');
            });
        }
    }
}

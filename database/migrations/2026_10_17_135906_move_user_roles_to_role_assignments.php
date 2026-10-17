<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Maps the legacy users.role values to the Grant role values. The values are
     * hardcoded rather than read from the enums, so the migration keeps working
     * even if the enums change later.
     *
     * @var array<string, string>
     */
    private const array ROLE_MAP = [
        'ADMIN' => 'super-admin',
        'USER' => 'warehouse-volunteer',
    ];

    /**
     * Move every user's legacy role into role_assignments, then drop the column.
     * Users that already have a global role assignment are left untouched.
     */
    public function up(): void {
        DB::table('users')->select('id', 'role')->orderBy('id')->each(function (object $user): void {
            $role = self::ROLE_MAP[strtoupper((string) $user->role)] ?? null;

            if ($role === null) {
                return;
            }

            $hasGlobalAssignment = DB::table('role_assignments')
                ->where('user_id', $user->id)
                ->whereNull('scopeable_type')
                ->whereNull('scopeable_id')
                ->exists();

            if (! $hasGlobalAssignment) {
                DB::table('role_assignments')->insert([
                    'user_id' => $user->id,
                    'role' => $role,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }

    /**
     * Restore the legacy column from the global role assignments.
     * The assignments themselves are kept, since some may have been added manually.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user');
        });

        $admins = DB::table('role_assignments')
            ->where('role', self::ROLE_MAP['ADMIN'])
            ->whereNull('scopeable_type')
            ->whereNull('scopeable_id')
            ->pluck('user_id');

        DB::table('users')->update(['role' => 'USER']);
        DB::table('users')->whereIn('id', $admins)->update(['role' => 'ADMIN']);
    }
};

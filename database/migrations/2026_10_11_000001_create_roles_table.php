<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('permissions')->nullable();
            // The built-in Administrator role always has every permission
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained()->nullOnDelete();
        });

        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => Role::ADMIN_NAME,
            'permissions' => json_encode([]),
            'is_admin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Everyone who could sign in before keeps full access
        DB::table('users')->update(['role_id' => $adminRoleId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};

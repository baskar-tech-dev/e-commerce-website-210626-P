<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20)->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('avatar', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->softDeletes();
        });

        // Backfill existing users
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $uuid = (string) Str::uuid();
            $name = $user->name ?? '';
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';

            DB::table('users')->where('id', $user->id)->update([
                'uuid' => $uuid,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'first_name',
                'last_name',
                'phone',
                'phone_verified_at',
                'avatar',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};

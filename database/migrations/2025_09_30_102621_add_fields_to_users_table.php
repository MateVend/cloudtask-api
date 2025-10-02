// FILE 6: database/migrations/2024_01_01_000006_add_fields_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('avatar');
            $table->foreignId('current_organization_id')->nullable()->after('role')->constrained('organizations')->onDelete('set null');
            $table->timestamp('last_active_at')->nullable()->after('current_organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_organization_id']);
            $table->dropColumn(['avatar', 'role', 'current_organization_id', 'last_active_at']);
        });
    }
};

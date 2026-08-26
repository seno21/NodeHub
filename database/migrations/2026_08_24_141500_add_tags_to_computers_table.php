<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            if (!Schema::hasColumn('computers', 'tags')) {
                $table->string('tags')->nullable()->after('location');
            }
            if (!Schema::hasColumn('computers', 'ssh_port')) {
                $table->unsignedSmallInteger('ssh_port')->default(22);
            }
            if (!Schema::hasColumn('computers', 'ssh_user')) {
                $table->string('ssh_user', 100)->default('xubuntu');
            }
            if (!Schema::hasColumn('computers', 'ssh_password')) {
                $table->text('ssh_password')->nullable();
            }
            if (!Schema::hasColumn('computers', 'refresh_command')) {
                $table->text('refresh_command')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $columnsToDrop = array_filter(['tags', 'ssh_port', 'ssh_user', 'ssh_password', 'refresh_command'], fn (string $col) => Schema::hasColumn('computers', $col));
            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};

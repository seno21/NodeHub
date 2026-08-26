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
            if (!Schema::hasColumn('computers', 'ssh_port')) {
                $table->unsignedSmallInteger('ssh_port')->default(22)->after('vnc_port');
            }
            if (!Schema::hasColumn('computers', 'ssh_user')) {
                $table->string('ssh_user', 100)->nullable()->after('os_type');
            }
            if (!Schema::hasColumn('computers', 'ssh_password')) {
                $table->text('ssh_password')->nullable()->after('ssh_user');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $columnsToDrop = array_filter(['ssh_port', 'ssh_user', 'ssh_password'], fn (string $col) => Schema::hasColumn('computers', $col));
            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};

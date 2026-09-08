<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up existing duplicate IP addresses in production database before applying unique constraint
        $duplicates = DB::table('computers')
            ->select('ip_address')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('ip_address');

        foreach ($duplicates as $ip) {
            $computers = DB::table('computers')
                ->where('ip_address', $ip)
                ->orderBy('id', 'asc')
                ->get();

            $index = 1;
            foreach ($computers as $comp) {
                // Keep the first record as is
                if ($index === 1) {
                    $index++;
                    continue;
                }

                // Append suffix to duplicate records to make them unique
                $suffix = "-dup-{$comp->id}";
                $baseIp = substr((string) $comp->ip_address, 0, 45 - strlen($suffix));
                $newIp = $baseIp . $suffix;

                DB::table('computers')
                    ->where('id', $comp->id)
                    ->update(['ip_address' => $newIp]);

                $index++;
            }
        }

        // 2. Add unique constraint safely
        Schema::table('computers', function (Blueprint $table) {
            $table->unique('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->dropUnique(['ip_address']);
        });
    }
};

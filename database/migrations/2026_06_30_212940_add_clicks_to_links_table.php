<?php
// database/migrations/2024_01_01_add_clicks_to_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            // Add clicks column if it doesn't exist
            if (!Schema::hasColumn('links', 'clicks')) {
                $table->integer('clicks')->default(0)->after('is_active');
            }
            
            // Remove deleted_at column if it exists (since we're not using soft deletes)
            if (Schema::hasColumn('links', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('clicks');
            $table->softDeletes();
        });
    }
};
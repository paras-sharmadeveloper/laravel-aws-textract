<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Set once the internal team is emailed, so a resync never emails them again
            $table->timestamp('team_notified_at')->nullable()->after('affiliate_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', fn(Blueprint $table) => $table->dropColumn('team_notified_at'));
    }
};

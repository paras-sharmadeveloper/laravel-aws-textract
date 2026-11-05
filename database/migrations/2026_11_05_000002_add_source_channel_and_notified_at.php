<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            // Label of Pipedrive's deal "Source channel" option, e.g. "Affiliate - MCA Broker"
            $table->string('source_channel')->nullable()->after('phone');
        });

        Schema::table('leads', function (Blueprint $table) {
            // Set once the affiliate is emailed, so a resync never emails them again
            $table->timestamp('affiliate_notified_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', fn(Blueprint $table) => $table->dropColumn('source_channel'));
        Schema::table('leads', fn(Blueprint $table) => $table->dropColumn('affiliate_notified_at'));
    }
};

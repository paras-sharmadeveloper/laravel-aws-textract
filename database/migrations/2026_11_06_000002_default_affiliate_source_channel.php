<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Affiliates created before the source channel existed get the default
        DB::table('affiliates')->whereNull('source_channel')->orWhere('source_channel', '')
            ->update(['source_channel' => 'Affiliate - MCA Broker']);
    }

    public function down(): void
    {
        //
    }
};

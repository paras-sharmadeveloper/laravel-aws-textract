<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // The OCR'd result handed to ParseAndCreateLeadJob, so Pipedrive can be re-synced without paying for OCR again
            $table->json('ocr_result')->nullable()->after('payload');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('ocr_result');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deal_folder')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->unsignedInteger('locations')->nullable();
            $table->boolean('new_location')->default(false);

            // processing | completed | failed
            $table->string('status')->default('processing')->index();
            // upload | ocr | pipedrive | attachments | done
            $table->string('stage')->default('upload');
            $table->text('error')->nullable();

            // The job payload handed to ProcessOcrJob, kept so the lead can be re-run
            $table->json('payload')->nullable();

            $table->unsignedBigInteger('pipedrive_person_id')->nullable();
            $table->unsignedBigInteger('pipedrive_org_id')->nullable();
            $table->unsignedBigInteger('pipedrive_deal_id')->nullable();
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('file_name');
            $table->string('s3_key');
            // pending | attached | failed
            $table->string('attach_status')->default('pending');
            $table->text('attach_error')->nullable();
            $table->timestamp('attached_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 's3_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_documents');
        Schema::dropIfExists('leads');
    }
};

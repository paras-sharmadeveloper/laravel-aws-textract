<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Saving the email settings stores the default text, so update saved copies too
        DB::table('settings')->whereIn('key', ['mail.subject', 'mail.body', 'mail.team_subject', 'mail.team_body'])
            ->get(['id', 'value'])
            ->each(fn($row) => DB::table('settings')->where('id', $row->id)->update([
                'value' => str_replace('New submission received', 'New Submission Received', (string) $row->value),
            ]));
    }

    public function down(): void
    {
        //
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ses_submissions MODIFY COLUMN status ENUM('draft','ready','sent','partially_sent','acknowledged','failed','rejected') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ses_submissions MODIFY COLUMN status ENUM('draft','ready','sent','acknowledged','failed','rejected') NOT NULL DEFAULT 'draft'");
    }
};

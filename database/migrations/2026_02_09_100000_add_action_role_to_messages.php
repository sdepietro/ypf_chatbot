<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN role ENUM('human','bot','system','action') DEFAULT 'human'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN role ENUM('human','bot','system') DEFAULT 'human'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->decimal('total_llm_cost', 10, 6)->default(0)->after('total_cost');
            $table->decimal('total_tts_cost', 10, 6)->default(0)->after('total_llm_cost');
            $table->decimal('total_stt_cost', 10, 6)->default(0)->after('total_tts_cost');
        });

        // Migrate existing data: move total_cost to total_llm_cost
        DB::statement('UPDATE chats SET total_llm_cost = total_cost WHERE total_cost > 0');
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['total_llm_cost', 'total_tts_cost', 'total_stt_cost']);
        });
    }
};

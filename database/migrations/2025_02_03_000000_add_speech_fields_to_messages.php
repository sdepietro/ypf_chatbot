<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // STT (Speech-to-Text) fields
            $table->string('stt_provider', 20)->nullable()->after('meta');
            $table->string('stt_model', 50)->nullable()->after('stt_provider');
            $table->integer('stt_duration_ms')->nullable()->after('stt_model');
            $table->decimal('stt_cost', 10, 6)->nullable()->after('stt_duration_ms');

            // TTS (Text-to-Speech) fields
            $table->string('tts_provider', 20)->nullable()->after('stt_cost');
            $table->string('tts_model', 50)->nullable()->after('tts_provider');
            $table->string('tts_voice', 20)->nullable()->after('tts_model');
            $table->integer('tts_duration_ms')->nullable()->after('tts_voice');
            $table->integer('tts_characters')->nullable()->after('tts_duration_ms');
            $table->decimal('tts_cost', 10, 6)->nullable()->after('tts_characters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'stt_provider',
                'stt_model',
                'stt_duration_ms',
                'stt_cost',
                'tts_provider',
                'tts_model',
                'tts_voice',
                'tts_duration_ms',
                'tts_characters',
                'tts_cost',
            ]);
        });
    }
};

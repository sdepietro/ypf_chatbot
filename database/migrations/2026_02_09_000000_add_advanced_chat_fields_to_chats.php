<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->string('chat_type', 20)->default('simple')->after('status');
            $table->json('scene_data')->nullable()->after('chat_type');
            $table->string('scene_image_path')->nullable()->after('scene_data');
            $table->decimal('total_image_cost', 10, 6)->default(0)->after('total_stt_cost');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['chat_type', 'scene_data', 'scene_image_path', 'total_image_cost']);
        });
    }
};

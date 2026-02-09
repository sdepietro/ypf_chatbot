<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Config::updateOrCreate(
            ['tag' => 'chat-type'],
            ['value' => 'simple', 'description' => 'Tipo de chat: simple o advanced']
        );
    }

    public function down(): void
    {
        Config::where('tag', 'chat-type')->delete();
    }
};

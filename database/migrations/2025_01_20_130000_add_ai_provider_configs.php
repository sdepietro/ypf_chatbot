<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // AI provider selection
        Config::setValue('ai-provider', 'openai', 'Active AI provider (openai, deepseek)');

        // DeepSeek configs
        Config::setValue('deepseek-api-key', '', 'DeepSeek API key');
        Config::setValue('deepseek-model', 'deepseek-chat', 'DeepSeek model (deepseek-chat, deepseek-reasoner)');
        Config::setValue('deepseek-temperature', '0.7', 'DeepSeek temperature (0.0 - 2.0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Config::where('tag', 'ai-provider')->delete();
        Config::where('tag', 'like', 'deepseek-%')->delete();
    }
};

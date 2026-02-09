<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->unique()->constrained('chats')->onDelete('cascade');
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->json('criteria_results');
            $table->text('overall_feedback')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};

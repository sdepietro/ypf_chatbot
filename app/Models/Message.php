<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'chat_id',
        'role',
        'content',
        'prompt_tokens',
        'completion_tokens',
        'cost',
        'provider',
        'model',
        'meta',
        // STT fields
        'stt_provider',
        'stt_model',
        'stt_duration_ms',
        'stt_cost',
        // TTS fields
        'tts_provider',
        'tts_model',
        'tts_voice',
        'tts_duration_ms',
        'tts_characters',
        'tts_cost',
    ];

    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'cost' => 'decimal:6',
        'meta' => 'array',
        // STT casts
        'stt_duration_ms' => 'integer',
        'stt_cost' => 'decimal:6',
        // TTS casts
        'tts_duration_ms' => 'integer',
        'tts_characters' => 'integer',
        'tts_cost' => 'decimal:6',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function isHuman(): bool
    {
        return $this->role === 'human';
    }

    public function isBot(): bool
    {
        return $this->role === 'bot';
    }

    public function isSystem(): bool
    {
        return $this->role === 'system';
    }

    public function getTotalTokens(): int
    {
        return ($this->prompt_tokens ?? 0) + ($this->completion_tokens ?? 0);
    }
}

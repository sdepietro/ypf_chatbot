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
    ];

    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'cost' => 'decimal:6',
        'meta' => 'array',
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

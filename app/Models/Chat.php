<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'agent_id',
        'title',
        'status',
        'total_tokens',
        'total_cost',
        'total_llm_cost',
        'total_tts_cost',
        'total_stt_cost',
    ];

    protected $casts = [
        'total_tokens' => 'integer',
        'total_cost' => 'decimal:6',
        'total_llm_cost' => 'decimal:6',
        'total_tts_cost' => 'decimal:6',
        'total_stt_cost' => 'decimal:6',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function addTokensAndCost(int $tokens, float $cost): void
    {
        $this->increment('total_tokens', $tokens);
        $this->increment('total_cost', $cost);
    }

    public function addLlmCost(int $tokens, float $cost): void
    {
        $this->increment('total_tokens', $tokens);
        $this->increment('total_cost', $cost);
        $this->increment('total_llm_cost', $cost);
    }

    public function addTtsCost(float $cost): void
    {
        $this->increment('total_cost', $cost);
        $this->increment('total_tts_cost', $cost);
    }

    public function addSttCost(float $cost): void
    {
        $this->increment('total_cost', $cost);
        $this->increment('total_stt_cost', $cost);
    }

    public function finish(): void
    {
        $this->update(['status' => 'finished']);
    }
}

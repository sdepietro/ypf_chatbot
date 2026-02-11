<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'system_prompt',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getRandomActive(): ?self
    {
        $recentAgentIds = Chat::orderByDesc('id')
            ->take(3)
            ->pluck('agent_id')
            ->unique()
            ->toArray();

        $agent = self::active()
            ->whereNotIn('id', $recentAgentIds)
            ->inRandomOrder()
            ->first();

        // Fallback: if all agents were used recently, pick any active one
        if (!$agent) {
            $agent = self::active()->inRandomOrder()->first();
        }

        return $agent;
    }
}

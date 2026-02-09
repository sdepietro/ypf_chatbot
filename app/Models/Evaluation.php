<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id',
        'overall_score',
        'criteria_results',
        'overall_feedback',
        'prompt_tokens',
        'completion_tokens',
        'cost',
        'provider',
        'model',
        'meta',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'criteria_results' => 'array',
        'cost' => 'decimal:6',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'meta' => 'array',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function isPassed(): bool
    {
        return $this->overall_score >= 70;
    }

    public function getCriterionResult(string $key): ?array
    {
        $criteria = $this->criteria_results ?? [];

        foreach ($criteria as $criterion) {
            if (($criterion['key'] ?? null) === $key) {
                return $criterion;
            }
        }

        return null;
    }
}

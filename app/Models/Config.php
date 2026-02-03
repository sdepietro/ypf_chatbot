<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tag',
        'value',
        'description',
    ];

    public static function getValue(string $tag, $default = null)
    {
        $config = self::where('tag', $tag)->first();
        return $config?->value ?? $default;
    }

    public static function setValue(string $tag, $value, ?string $description = null): self
    {
        return self::updateOrCreate(
            ['tag' => $tag],
            ['value' => $value, 'description' => $description]
        );
    }
}

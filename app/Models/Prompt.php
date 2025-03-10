<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prompt extends Model
{
    protected $fillable = [
        'name',
        'content',
        'category',
        'rating',
        'usage_count',
        'is_public',
        'is_favorite',
    ];

    protected $casts = [
        'rating' => 'float',
        'usage_count' => 'integer',
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
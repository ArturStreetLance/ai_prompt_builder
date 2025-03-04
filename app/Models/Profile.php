<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'avatar',
        'bio',
        'website',
        'notifications_enabled',
        'public_profile',
        'theme',
        'prompts_created',
        'total_usage',
        'rating',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'public_profile' => 'boolean',
        'rating' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
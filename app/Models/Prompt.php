<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'category',
        'is_public',
        'user_id',
        'rating',
        'is_favorite'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
        'rating' => 'float'
    ];

    /**
     * Получить пользователя, которому принадлежит промпт
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
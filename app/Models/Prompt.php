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
        'is_favorite',
        'usage_count'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
        'rating' => 'float',
        'usage_count' => 'integer'
    ];

    /**
     * Получить пользователя, которому принадлежит промпт
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->belongsToMany(PromptHistory::class, 'prompt_history_items')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function incrementUsageCount()
    {
        $this->increment('usage_count');
    }

    public static function getPopular($limit = 10)
    {
        return static::orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getUserFrequent($userId, $limit = 5)
    {
        return static::whereHas('histories', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->withCount(['histories' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->orderBy('histories_count', 'desc')
        ->limit($limit)
        ->get();
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromptTemplate extends Model
{
    protected $fillable = ['name', 'content', 'version', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function updateAverageRating(): void
    {
        $this->rating = $this->ratings()->avg('score') ?? 0;
        $this->save();
    }
} 
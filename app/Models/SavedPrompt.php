<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPrompt extends Model
{
    protected $fillable = ['name', 'template_ids', 'compiled_content', 'user_id'];

    protected $casts = [
        'template_ids' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 
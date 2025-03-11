<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromptHistory extends Model
{
    protected $fillable = [
        'user_id',
        'final_prompt'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompts(): BelongsToMany
    {
        return $this->belongsToMany(Prompt::class, 'prompt_history_items')
            ->withPivot('position')
            ->withTimestamps();
    }
} 
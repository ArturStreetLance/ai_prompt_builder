<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function promptTemplates(): BelongsToMany
    {
        return $this->belongsToMany(PromptTemplate::class);
    }
} 
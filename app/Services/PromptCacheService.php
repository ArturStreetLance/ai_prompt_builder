<?php

namespace App\Services;

use App\Models\PromptTemplate;
use Illuminate\Support\Facades\Cache;

class PromptCacheService
{
    public function getCachedPrompt(array $templateIds)
    {
        $cacheKey = 'prompt_' . implode('_', $templateIds);
        
        return Cache::remember($cacheKey, 3600, function () use ($templateIds) {
            return $this->compilePrompt($templateIds);
        });
    }

    private function compilePrompt(array $templateIds)
    {
        $templates = PromptTemplate::whereIn('id', $templateIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $templateIds) . ')')
            ->get();

        return $templates->pluck('content')->join("\n");
    }

    public function invalidateCache(array $templateIds)
    {
        $cacheKey = 'prompt_' . implode('_', $templateIds);
        Cache::forget($cacheKey);
    }
} 
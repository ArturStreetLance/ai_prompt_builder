<?php

namespace App\Observers;

use App\Models\Prompt;
use Illuminate\Support\Facades\Cache;

class PromptObserver
{
    /**
     * Handle the Prompt "updated" event.
     */
    public function updated(Prompt $prompt): void
    {
        // Если изменился рейтинг или количество использований, очищаем кеш популярных промптов
        if ($prompt->wasChanged(['rating', 'usage_count', 'is_favorite'])) {
            Cache::tags(['prompts'])->flush();
        }
    }

    /**
     * Handle the Prompt "deleted" event.
     */
    public function deleted(Prompt $prompt): void
    {
        Cache::tags(['prompts'])->flush();
    }

    /**
     * Handle the Prompt "restored" event.
     */
    public function restored(Prompt $prompt): void
    {
        Cache::tags(['prompts'])->flush();
    }
} 
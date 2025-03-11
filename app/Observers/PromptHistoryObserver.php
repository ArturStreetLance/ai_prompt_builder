<?php

namespace App\Observers;

use App\Models\PromptHistory;
use App\Models\Prompt;

class PromptHistoryObserver
{
    /**
     * Handle the PromptHistory "created" event.
     */
    public function created(PromptHistory $promptHistory): void
    {
        // Получаем все промпты, связанные с этой историей
        $promptIds = $promptHistory->prompts->pluck('id');
        
        // Увеличиваем счетчик использования для каждого промпта
        Prompt::whereIn('id', $promptIds)->increment('usage_count');
    }

    /**
     * Handle the PromptHistory "deleted" event.
     */
    public function deleted(PromptHistory $promptHistory): void
    {
        // При удалении истории уменьшаем счетчики использования
        $promptIds = $promptHistory->prompts->pluck('id');
        
        Prompt::whereIn('id', $promptIds)->decrement('usage_count');
    }
} 
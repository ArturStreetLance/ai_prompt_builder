<?php

namespace App\Services;

use App\Contracts\Services\PromptHistoryServiceInterface;
use App\Models\Prompt;
use App\Models\PromptHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PromptHistoryService implements PromptHistoryServiceInterface
{
    /**
     * @inheritDoc
     */
    public function recordUsage(array $usedPrompts, string $finalPrompt): PromptHistory
    {
        return DB::transaction(function () use ($usedPrompts, $finalPrompt) {
            // Создаем запись в истории
            $history = PromptHistory::create([
                'user_id' => Auth::id(),
                'final_prompt' => $finalPrompt
            ]);

            // Связываем промпты с историей
            $attachData = collect($usedPrompts)->mapWithKeys(function ($promptId, $position) {
                return [$promptId => ['position' => $position]];
            })->all();

            $history->prompts()->attach($attachData);

            return $history->load('prompts');
        });
    }

    /**
     * @inheritDoc
     */
    public function getPopularPrompts(int $limit = 10): Collection
    {
        return Cache::tags(['prompts'])->remember('popular_prompts_' . $limit, now()->addHours(1), function () use ($limit) {
            return Prompt::getPopular($limit);
        });
    }

    /**
     * @inheritDoc
     */
    public function getUserFrequentPrompts(int $limit = 5): Collection
    {
        return Prompt::getUserFrequent(Auth::id(), $limit);
    }
} 
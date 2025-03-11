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
    public function recordUsage(array $promptIds, string $finalPrompt): PromptHistory
    {
        DB::beginTransaction();
        try {
            // Создаем запись в истории
            $history = Auth::user()->promptHistories()->create([
                'final_prompt' => $finalPrompt,
                'used_prompts' => $promptIds
            ]);

            // Прикрепляем промпты к истории с позициями
            $promptsWithPosition = collect($promptIds)->map(function($id, $index) {
                return ['prompt_id' => $id, 'position' => $index];
            })->toArray();
            
            $history->prompts()->attach($promptsWithPosition);

            // Обновляем счетчики использования одним запросом
            DB::table('prompts')
                ->whereIn('id', $promptIds)
                ->update([
                    'usage_count' => DB::raw('usage_count + 1'),
                    'rating' => DB::raw('(rating * usage_count + 5) / (usage_count + 1)')
                ]);

            // Очищаем кеш популярных промптов
            Cache::tags(['prompts'])->flush();

            DB::commit();
            return $history;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getPopularPrompts(int $limit = 10): Collection
    {
        $cacheKey = 'popular_prompts_' . $limit;
        
        // Если кеш пуст, получаем и кешируем данные
        if (!Cache::tags(['prompts'])->has($cacheKey)) {
            $prompts = Prompt::getPopular($limit);
            Cache::tags(['prompts'])->put($cacheKey, $prompts, now()->addHours(1));
            return $prompts;
        }
        
        // Возвращаем данные из кеша
        return Cache::tags(['prompts'])->get($cacheKey);
    }

    /**
     * @inheritDoc
     */
    public function getUserFrequentPrompts(int $limit = 5): Collection
    {
        return Prompt::getUserFrequent(Auth::id(), $limit);
    }
} 
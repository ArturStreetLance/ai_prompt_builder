<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'is_public' => 'boolean'
        ]);

        $prompt = $request->user()->prompts()->create($validated);

        return back()->with('message', 'Промпт успешно создан');
    }

    /**
     * Обновить рейтинг промпта
     */
    public function updateRating(Request $request, Prompt $prompt)
    {
        //dd($request->all());
        try {
            \Log::info('Request data:', [
                'all' => $request->all(),
                'prompt_id' => $prompt->id,
                'prompt_data' => $prompt->toArray()
            ]);

            $request->validate([
                'rating' => 'required|numeric|min:0|max:5'
            ]);

            \Log::info('Updating rating', [
                'prompt_id' => $prompt->id,
                'old_rating' => $prompt->rating,
                'new_rating' => $request->rating
            ]);

            $result = $prompt->update([
                'rating' => $request->rating
            ]);

            \Log::info('Update result:', [
                'success' => $result,
                'new_prompt_data' => $prompt->fresh()->toArray()
            ]);

            return response()->json([
                'success' => true,
                'rating' => $prompt->rating
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating rating', [
                'prompt_id' => $prompt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении рейтинга: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Переключить статус избранного для промпта
     */
    public function toggleFavorite(Prompt $prompt)
    {
        try {
            \Log::info('Toggling favorite', [
                'prompt_id' => $prompt->id,
                'old_state' => $prompt->is_favorite
            ]);

            $prompt->update([
                'is_favorite' => !$prompt->is_favorite
            ]);

            return response()->json([
                'success' => true,
                'is_favorite' => $prompt->is_favorite
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite', [
                'prompt_id' => $prompt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при изменении статуса избранного'
            ], 500);
        }
    }

    public function incrementUsage(Prompt $prompt)
    {
        $prompt->increment('usage_count');
        // Пересчитываем рейтинг
        $prompt->rating = ($prompt->rating * ($prompt->usage_count - 1) + 5) / $prompt->usage_count;
        $prompt->save();

        return back();
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'promptIds' => 'array'
        ]);

        // Здесь можно добавить логику сохранения комбинации промптов
        // Например, сохранить в таблицу prompt_combinations

        // Обновляем рейтинг для всех использованных промптов
        if (!empty($validated['promptIds'])) {
            Prompt::whereIn('id', $validated['promptIds'])->each(function ($prompt) {
                $prompt->increment('usage_count');
                $prompt->rating = ($prompt->rating * ($prompt->usage_count - 1) + 5) / $prompt->usage_count;
                $prompt->save();
            });
        }

        return back()->with('message', 'Промпты успешно отправлены');
    }
} 
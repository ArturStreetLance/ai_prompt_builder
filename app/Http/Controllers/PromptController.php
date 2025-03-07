<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;

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

    public function toggleFavorite(Prompt $prompt)
    {
        $prompt->is_favorite = !$prompt->is_favorite;
        $prompt->save();

        return back();
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
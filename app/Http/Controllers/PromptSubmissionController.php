<?php

namespace App\Http\Controllers;

use App\Services\PromptHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PromptSubmissionController extends Controller
{
    private $promptHistoryService;

    public function __construct(PromptHistoryService $promptHistoryService)
    {
        $this->promptHistoryService = $promptHistoryService;
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'final_prompt' => 'required|string',
            'used_prompts' => 'required|array',
            'used_prompts.*' => 'exists:prompts,id'
        ]);

        // Записываем использование промптов
        $this->promptHistoryService->recordUsage(
            $validated['used_prompts'],
            $validated['final_prompt']
        );

        // Получаем обновленные популярные промпты
        $popularPrompts = Cache::remember('popular_prompts', 3600, function () {
            return $this->promptHistoryService->getPopularPrompts();
        });

        // Получаем часто используемые промпты пользователя
        $frequentPrompts = $this->promptHistoryService->getUserFrequentPrompts();

        return response()->json([
            'success' => true,
            'popular_prompts' => $popularPrompts,
            'frequent_prompts' => $frequentPrompts
        ]);
    }

    public function getPopularPrompts()
    {
        $popularPrompts = Cache::remember('popular_prompts', 3600, function () {
            return $this->promptHistoryService->getPopularPrompts();
        });

        return response()->json([
            'prompts' => $popularPrompts
        ]);
    }

    public function getUserFrequentPrompts()
    {
        $frequentPrompts = $this->promptHistoryService->getUserFrequentPrompts();

        return response()->json([
            'prompts' => $frequentPrompts
        ]);
    }
} 
<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Services\PromptHistoryServiceInterface;
use Inertia\Inertia;

class PromptController extends Controller
{
    protected $promptHistoryService;

    public function __construct(PromptHistoryServiceInterface $promptHistoryService)
    {
        $this->promptHistoryService = $promptHistoryService;
    }

    /**
     * Отображение страницы с промптами
     */
    public function index()
    {
        return Inertia::render('Prompts/Index', [
            'popularPrompts' => $this->promptHistoryService->getPopularPrompts(10),
            'frequentPrompts' => $this->promptHistoryService->getUserFrequentPrompts(5),
            'userPrompts' => Auth::user()->prompts()->latest()->get()
        ]);
    }

    /**
     * Создание нового промпта
     */
    public function store(Request $request)
    {
        // Валидация
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:100',
            'category' => 'required|string|in:Копирайтинг,Разработка,Маркетинг,SEO,Творчество',
            'content' => 'required|string|min:10',
            'is_public' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        try {
            // Создаем промпт через отношение пользователя
            $prompt = Auth::user()->prompts()->create([
                'name' => $request->name,
                'category' => $request->category,
                'content' => $request->content,
                'is_public' => $request->is_public,
                'rating' => 0,
                'is_favorite' => false
            ]);

            return back()->with([
                'success' => true,
                'message' => 'Промпт успешно создан',
                'prompt' => $prompt
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'success' => false,
                'message' => 'Ошибка при создании промпта'
            ]);
        }
    }

    /**
     * Обновление рейтинга промпта
     */
    public function updateRating(Request $request, Prompt $prompt)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:0|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректное значение рейтинга'
            ], 422);
        }

        try {
            $prompt->update(['rating' => $request->rating]);
            
            return response()->json([
                'success' => true,
                'message' => 'Рейтинг обновлен',
                'rating' => $prompt->rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении рейтинга'
            ], 500);
        }
    }

    /**
     * Переключение статуса избранного
     */
    public function toggleFavorite(Prompt $prompt)
    {
        try {
            $prompt->update(['is_favorite' => !$prompt->is_favorite]);
            
            return response()->json([
                'success' => true,
                'message' => 'Статус избранного обновлен',
                'is_favorite' => $prompt->is_favorite
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении статуса избранного'
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

        if (!empty($validated['promptIds'])) {
            // Используем сервис для записи использования промптов
            $this->promptHistoryService->recordUsage($validated['promptIds'], $validated['content']);
        }

        return back()->with('message', 'Промпты успешно отправлены');
    }
} 
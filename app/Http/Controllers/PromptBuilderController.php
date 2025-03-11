<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Contracts\Services\PromptHistoryServiceInterface;

class PromptBuilderController extends Controller
{
    protected $promptHistoryService;

    public function __construct(PromptHistoryServiceInterface $promptHistoryService)
    {
        $this->middleware('auth');
        $this->promptHistoryService = $promptHistoryService;
    }

    public function index()
    {
        $user = Auth::user();
 // Если кеша нет, получаем из БД и кешируем
        // if (!$popularPrompts) {
        //    $popularPrompts = Prompt::where('is_public', true)
        //        ->orderByRaw('COALESCE(usage_count, 0) DESC')
        //        ->orderByRaw('COALESCE(rating, 0) DESC')
        //        ->take(15)
        //        ->get();

          //  Cache::tags(['prompts'])->put($cacheKey, $popularPrompts, now()->addHours(1));
        //}
        return Inertia::render('PromptBuilder', [
            'user' => $user,
            'prompts' => $user->prompts()
                ->orderBy('created_at', 'desc')
                ->get(),
            'popularPrompts' => $this->promptHistoryService->getPopularPrompts(15),
            'frequentPrompts' => $this->promptHistoryService->getUserFrequentPrompts(5),
        ]);
    }
}

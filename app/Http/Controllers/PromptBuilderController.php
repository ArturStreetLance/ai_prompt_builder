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
       /* dd($user->prompts()
            ->orderBy('created_at', 'desc')
            ->get());*/
        return Inertia::render('PromptBuilder', [
            'user' => $user,
            'prompts' => $user->prompts()
                ->orderBy('created_at', 'desc')
                ->get(),
            'popularPrompts' => Cache::tags(['prompts'])->get('popular_prompts_5') ?? 
                $this->promptHistoryService->getPopularPrompts(5),
            'frequentPrompts' => $this->promptHistoryService->getUserFrequentPrompts(5),
        ]);
    }
}

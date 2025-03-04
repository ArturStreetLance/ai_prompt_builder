<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PromptBuilderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return Inertia::render('PromptBuilder', [
            'user' => $user,
            'prompts' => $user->prompts()
                ->orderBy('created_at', 'desc')
                ->get(),
            'popularPrompts' => Prompt::where('is_public', true)
                ->orderBy('rating', 'desc')
                ->orderBy('usage_count', 'desc')
                ->take(5)
                ->get(),
        ]);
    }
} 
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\PromptCacheService;

class PromptTemplateController extends Controller
{
    public function index()
    {
        return response()->json(
            PromptTemplate::with('categories')->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'required|string',
            'categories' => 'array'
        ]);

        $template = PromptTemplate::create($validated);
        
        if (!empty($validated['categories'])) {
            $template->categories()->sync($validated['categories']);
        }

        return response()->json($template, 201);
    }

    public function show(PromptTemplate $promptTemplate)
    {
        return response()->json($promptTemplate->load('categories'));
    }

    public function update(Request $request, PromptTemplate $promptTemplate)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'content' => 'string',
            'version' => 'string',
            'categories' => 'array'
        ]);

        $promptTemplate->update($validated);
        
        if (isset($validated['categories'])) {
            $promptTemplate->categories()->sync($validated['categories']);
        }

        return response()->json($promptTemplate);
    }

    public function destroy(PromptTemplate $promptTemplate)
    {
        $promptTemplate->delete();
        return response()->json(null, 204);
    }

    public function compile(Request $request)
    {
        $validated = $request->validate([
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:prompt_templates,id',
            'name' => 'required|string|max:255'
        ]);

        $promptService = new PromptCacheService();
        $compiledPrompt = $promptService->getCachedPrompt($validated['template_ids']);

        // Сохраняем промпт
        $savedPrompt = auth()->user()->savedPrompts()->create([
            'name' => $validated['name'],
            'template_ids' => $validated['template_ids'],
            'compiled_content' => $compiledPrompt
        ]);

        return response()->json([
            'prompt' => $compiledPrompt,
            'saved' => $savedPrompt
        ]);
    }

    public function saved()
    {
        $savedPrompts = auth()->user()->savedPrompts;
        return response()->json($savedPrompts);
    }

    public function export(Request $request)
    {
        $promptIds = $request->input('prompt_ids');
        $prompts = PromptTemplate::whereIn('id', $promptIds)
            ->with('categories')
            ->get();

        return response()->json([
            'prompts' => $prompts,
            'version' => '1.0',
            'exported_at' => now()
        ]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'prompts' => 'required|array',
            'version' => 'required|string'
        ]);

        $imported = collect($validated['prompts'])->map(function ($prompt) {
            $newPrompt = PromptTemplate::create([
                'name' => $prompt['name'],
                'content' => $prompt['content'],
                'version' => $prompt['version'],
                'metadata' => $prompt['metadata'] ?? null
            ]);

            if (!empty($prompt['categories'])) {
                $newPrompt->categories()->sync(
                    collect($prompt['categories'])->pluck('id')
                );
            }

            return $newPrompt;
        });

        return response()->json($imported);
    }
} 
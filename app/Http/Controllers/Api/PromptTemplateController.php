<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
} 
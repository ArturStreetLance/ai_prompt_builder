<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function rate(Request $request, PromptTemplate $promptTemplate)
    {
        $validated = $request->validate([
            'score' => 'required|integer|between:1,5',
            'comment' => 'nullable|string'
        ]);

        $rating = $promptTemplate->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        $promptTemplate->updateAverageRating();

        return response()->json($rating);
    }
} 
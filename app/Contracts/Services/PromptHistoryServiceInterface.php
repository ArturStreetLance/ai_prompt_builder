<?php

namespace App\Contracts\Services;

use App\Models\PromptHistory;
use Illuminate\Database\Eloquent\Collection;

interface PromptHistoryServiceInterface
{
    /**
     * Record the usage of prompts and create history entry
     *
     * @param array $usedPrompts Array of prompt IDs with their positions
     * @param string $finalPrompt The final compiled prompt text
     * @return PromptHistory
     */
    public function recordUsage(array $usedPrompts, string $finalPrompt): PromptHistory;

    /**
     * Get popular prompts across all users
     *
     * @param int $limit
     * @return Collection
     */
    public function getPopularPrompts(int $limit = 10): Collection;

    /**
     * Get frequently used prompts for current user
     *
     * @param int $limit
     * @return Collection
     */
    public function getUserFrequentPrompts(int $limit = 5): Collection;
} 
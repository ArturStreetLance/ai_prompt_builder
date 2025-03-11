<?php

namespace Tests\Unit\Services;

use App\Models\Prompt;
use App\Models\PromptHistory;
use App\Models\User;
use App\Services\PromptHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private PromptHistoryService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(PromptHistoryService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_record_prompt_usage()
    {
        // Arrange
        $prompts = Prompt::factory(3)->create();
        $usedPrompts = $prompts->mapWithKeys(function ($prompt, $index) {
            return [$index => $prompt->id];
        })->all();
        $finalPrompt = 'Test final prompt';

        // Act
        $history = $this->service->recordUsage($usedPrompts, $finalPrompt);

        // Assert
        $this->assertDatabaseHas('prompt_histories', [
            'user_id' => $this->user->id,
            'final_prompt' => $finalPrompt
        ]);

        foreach ($usedPrompts as $position => $promptId) {
            $this->assertDatabaseHas('prompt_history_items', [
                'prompt_history_id' => $history->id,
                'prompt_id' => $promptId,
                'position' => $position
            ]);
        }
    }

    /** @test */
    public function it_can_get_popular_prompts()
    {
        // Arrange
        $popularPrompt = Prompt::factory()->create(['usage_count' => 10]);
        $lessPopularPrompt = Prompt::factory()->create(['usage_count' => 5]);

        // Act
        $popularPrompts = $this->service->getPopularPrompts(2);

        // Assert
        $this->assertEquals(2, $popularPrompts->count());
        $this->assertEquals($popularPrompt->id, $popularPrompts->first()->id);
    }

    /** @test */
    public function it_can_get_user_frequent_prompts()
    {
        // Arrange
        $prompts = Prompt::factory(3)->create();
        $history = PromptHistory::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $history->prompts()->attach([
            $prompts[0]->id => ['position' => 0],
            $prompts[1]->id => ['position' => 1]
        ]);

        // Act
        $frequentPrompts = $this->service->getUserFrequentPrompts(2);

        // Assert
        $this->assertEquals(2, $frequentPrompts->count());
        $this->assertTrue($frequentPrompts->contains($prompts[0]));
        $this->assertTrue($frequentPrompts->contains($prompts[1]));
    }
} 
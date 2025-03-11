<?php

namespace Tests\Unit\Observers;

use App\Models\Prompt;
use App\Models\PromptHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptHistoryObserverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_increments_usage_count_when_history_created()
    {
        // Arrange
        $prompts = Prompt::factory(2)->create(['usage_count' => 0]);
        $history = PromptHistory::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Act
        $history->prompts()->attach([
            $prompts[0]->id => ['position' => 0],
            $prompts[1]->id => ['position' => 1]
        ]);

        // Assert
        $this->assertEquals(1, $prompts[0]->fresh()->usage_count);
        $this->assertEquals(1, $prompts[1]->fresh()->usage_count);
    }

    /** @test */
    public function it_decrements_usage_count_when_history_deleted()
    {
        // Arrange
        $prompts = Prompt::factory(2)->create(['usage_count' => 1]);
        $history = PromptHistory::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $history->prompts()->attach([
            $prompts[0]->id => ['position' => 0],
            $prompts[1]->id => ['position' => 1]
        ]);

        // Act
        $history->delete();

        // Assert
        $this->assertEquals(0, $prompts[0]->fresh()->usage_count);
        $this->assertEquals(0, $prompts[1]->fresh()->usage_count);
    }
} 
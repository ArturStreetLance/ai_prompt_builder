<?php

namespace Tests\Feature;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_submit_prompt_with_used_prompts()
    {
        // Arrange
        $this->actingAs($this->user);
        $prompts = Prompt::factory(2)->create();
        
        // Act
        $response = $this->postJson('/api/prompts/submit', [
            'final_prompt' => 'Test prompt content',
            'used_prompts' => [
                0 => $prompts[0]->id,
                1 => $prompts[1]->id
            ]
        ]);

        // Assert
        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'popular_prompts',
                'frequent_prompts'
            ]);

        $this->assertDatabaseHas('prompt_histories', [
            'user_id' => $this->user->id,
            'final_prompt' => 'Test prompt content'
        ]);
    }

    /** @test */
    public function user_cannot_submit_prompt_with_nonexistent_prompts()
    {
        // Arrange
        $this->actingAs($this->user);
        
        // Act
        $response = $this->postJson('/api/prompts/submit', [
            'final_prompt' => 'Test prompt content',
            'used_prompts' => [999]
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['used_prompts.0']);
    }

    /** @test */
    public function user_can_get_popular_prompts()
    {
        // Arrange
        $this->actingAs($this->user);
        $popularPrompt = Prompt::factory()->create(['usage_count' => 10]);
        $lessPopularPrompt = Prompt::factory()->create(['usage_count' => 5]);

        // Act
        $response = $this->getJson('/api/prompts/popular');

        // Assert
        $response->assertSuccessful()
            ->assertJsonStructure(['prompts'])
            ->assertJsonCount(2, 'prompts');

        $this->assertEquals(
            $popularPrompt->id,
            $response->json('prompts.0.id')
        );
    }

    /** @test */
    public function user_can_get_frequent_prompts()
    {
        // Arrange
        $this->actingAs($this->user);
        $prompts = Prompt::factory(2)->create();
        
        // Create history with these prompts
        $this->postJson('/api/prompts/submit', [
            'final_prompt' => 'Test prompt',
            'used_prompts' => [
                0 => $prompts[0]->id,
                1 => $prompts[1]->id
            ]
        ]);

        // Act
        $response = $this->getJson('/api/prompts/frequent');

        // Assert
        $response->assertSuccessful()
            ->assertJsonStructure(['prompts'])
            ->assertJsonCount(2, 'prompts');
    }
} 
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PromptTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_prompt_template()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/prompt-templates', [
                'name' => 'Test Template',
                'content' => 'Test content',
                'version' => '1.0'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'content',
                'version'
            ]);
    }

    public function test_can_rate_prompt_template()
    {
        $user = User::factory()->create();
        $template = PromptTemplate::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/prompt-templates/{$template->id}/rate", [
                'score' => 5,
                'comment' => 'Great template!'
            ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $template->fresh()->rating);
    }
} 
<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'name' => 'Laravel Basics',
            'description' => 'Intro to Laravel',
            'duration' => 8,
            'fee' => '15000.50',
            'difficulty' => 'medium',
            'is_active' => true,
        ];
    }

    public function test_index_returns_wrapped_list(): void
    {
        Course::create($this->validData());

        $this->getJson('/api/courses')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laravel Basics');
    }

    public function test_store_returns_201_with_resource(): void
    {
        $this->postJson('/api/courses', $this->validData())
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'duration', 'fee', 'difficulty', 'is_active']])
            ->assertJsonPath('data.is_active', true);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->postJson('/api/courses', [...$this->validData(), 'difficulty' => 'extreme', 'duration' => 0, 'fee' => -5])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['difficulty', 'duration', 'fee']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_show_returns_course(): void
    {
        $course = Course::create($this->validData());

        $this->getJson("/api/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $course->id);
    }

    public function test_show_missing_returns_404_json(): void
    {
        $this->getJson('/api/courses/999')->assertNotFound();
    }

    public function test_update_returns_200_with_new_values(): void
    {
        $course = Course::create($this->validData());

        $this->putJson("/api/courses/{$course->id}", [...$this->validData(), 'difficulty' => 'hard', 'is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.difficulty', 'hard')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_destroy_returns_204_with_no_body(): void
    {
        $course = Course::create($this->validData());

        $this->deleteJson("/api/courses/{$course->id}")->assertNoContent();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}

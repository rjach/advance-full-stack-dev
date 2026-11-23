<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{name: string, description: string, duration: int, fee: string, difficulty: string, is_active: string}
     */
    private function validCourseData(): array
    {
        return [
            'name' => 'Laravel Basics',
            'description' => 'Intro to Laravel',
            'duration' => 8,
            'fee' => '15000.50',
            'difficulty' => 'medium',
            'is_active' => '1',
        ];
    }

    public function test_course_list_shows_courses(): void
    {
        Course::create($this->validCourseData());

        $this->get('/courses')
            ->assertOk()
            ->assertSee('Laravel Basics');
    }

    public function test_create_form_renders(): void
    {
        $this->get('/courses/create')
            ->assertOk()
            ->assertSee('Create Course');
    }

    public function test_course_can_be_created(): void
    {
        $this->post('/courses', $this->validCourseData())
            ->assertRedirect('/courses')
            ->assertSessionHas('success', 'Course Laravel Basics created successfully!');

        $this->assertDatabaseHas('courses', ['name' => 'Laravel Basics', 'difficulty' => 'medium', 'is_active' => true]);
    }

    public function test_inactive_course_can_be_created(): void
    {
        $this->post('/courses', [...$this->validCourseData(), 'is_active' => '0']);

        $this->assertFalse(Course::first()->is_active);
    }

    public function test_create_requires_fields(): void
    {
        $this->post('/courses', [])
            ->assertSessionHasErrors(['name', 'duration', 'fee', 'difficulty', 'is_active']);
    }

    public function test_create_rejects_invalid_values(): void
    {
        $this->post('/courses', [...$this->validCourseData(), 'difficulty' => 'extreme', 'duration' => 0, 'fee' => -5])
            ->assertSessionHasErrors(['difficulty', 'duration', 'fee']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_course_detail_shows_course(): void
    {
        $course = Course::create($this->validCourseData());

        $this->get("/courses/{$course->id}")
            ->assertOk()
            ->assertSee('Intro to Laravel')
            ->assertSee('Medium');
    }

    public function test_missing_course_returns_404(): void
    {
        $this->get('/courses/999')->assertNotFound();
    }

    public function test_edit_form_renders(): void
    {
        $course = Course::create($this->validCourseData());

        $this->get("/courses/{$course->id}/edit")
            ->assertOk()
            ->assertSee('value="Laravel Basics"', false);
    }

    public function test_course_can_be_updated(): void
    {
        $course = Course::create($this->validCourseData());

        $this->put("/courses/{$course->id}", [...$this->validCourseData(), 'difficulty' => 'hard', 'is_active' => '0'])
            ->assertRedirect("/courses/{$course->id}")
            ->assertSessionHas('success', 'Course updated successfully!');

        $course->refresh();
        $this->assertSame('hard', $course->difficulty);
        $this->assertFalse($course->is_active);
    }

    public function test_course_can_be_deleted(): void
    {
        $course = Course::create($this->validCourseData());

        $this->delete("/courses/{$course->id}")
            ->assertRedirect('/courses')
            ->assertSessionHas('success', 'Course deleted successfully!');

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}

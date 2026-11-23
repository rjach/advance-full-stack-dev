<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validData(): array
    {
        return [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'date_of_birth' => '2000-01-15',
        ];
    }

    public function test_index_returns_wrapped_list(): void
    {
        Student::create($this->validData());

        $this->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'ada@example.com');
    }

    public function test_store_returns_201_with_resource(): void
    {
        $this->postJson('/api/students', $this->validData())
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'phone', 'address', 'date_of_birth', 'age']])
            ->assertJsonPath('data.date_of_birth', '2000-01-15');

        $this->assertDatabaseHas('students', ['email' => 'ada@example.com']);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->postJson('/api/students', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        Student::create($this->validData());

        $this->postJson('/api/students', $this->validData())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_show_returns_student(): void
    {
        $student = Student::create($this->validData());

        $this->getJson("/api/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $student->id);
    }

    public function test_show_missing_returns_404_json(): void
    {
        $this->getJson('/api/students/999')->assertNotFound();
    }

    public function test_update_keeps_own_email_and_returns_200(): void
    {
        $student = Student::create($this->validData());

        $this->putJson("/api/students/{$student->id}", [...$this->validData(), 'name' => 'Ada King'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada King');
    }

    public function test_update_rejects_email_of_another_student(): void
    {
        Student::create([...$this->validData(), 'email' => 'taken@example.com']);
        $student = Student::create($this->validData());

        $this->putJson("/api/students/{$student->id}", [...$this->validData(), 'email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_destroy_returns_204_with_no_body(): void
    {
        $student = Student::create($this->validData());

        $this->deleteJson("/api/students/{$student->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}

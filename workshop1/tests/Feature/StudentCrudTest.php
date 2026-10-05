<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{name: string, email: string, phone: string, address: string, date_of_birth: string}
     */
    private function validStudentData(): array
    {
        return [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'date_of_birth' => '2000-01-15',
        ];
    }

    public function test_student_list_shows_students(): void
    {
        Student::create($this->validStudentData());

        $this->get('/students')
            ->assertOk()
            ->assertSee('Ada Lovelace');
    }

    public function test_student_list_shows_empty_message(): void
    {
        $this->get('/students')
            ->assertOk()
            ->assertSee('No students found.');
    }

    public function test_create_form_renders(): void
    {
        $this->get('/students/create')
            ->assertOk()
            ->assertSee('Create Student');
    }

    public function test_student_can_be_created(): void
    {
        $this->post('/students', $this->validStudentData())
            ->assertRedirect('/students')
            ->assertSessionHas('success', 'Student Ada Lovelace created successfully!');

        $this->assertDatabaseHas('students', ['email' => 'ada@example.com']);
    }

    public function test_create_requires_name_email_and_phone(): void
    {
        $this->from('/students/create')
            ->post('/students', [])
            ->assertRedirect('/students/create')
            ->assertSessionHasErrors(['name', 'email', 'phone']);

        $this->assertDatabaseCount('students', 0);
    }

    public function test_create_rejects_duplicate_email(): void
    {
        Student::create($this->validStudentData());

        $this->post('/students', $this->validStudentData())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_student_detail_shows_student(): void
    {
        $student = Student::create($this->validStudentData());

        $this->get("/students/{$student->id}")
            ->assertOk()
            ->assertSee('ada@example.com');
    }

    public function test_missing_student_returns_404(): void
    {
        $this->get('/students/999')->assertNotFound();
    }

    public function test_edit_form_shows_current_values(): void
    {
        $student = Student::create($this->validStudentData());

        $this->get("/students/{$student->id}/edit")
            ->assertOk()
            ->assertSee('value="ada@example.com"', false);
    }

    public function test_student_can_be_updated_keeping_same_email(): void
    {
        $student = Student::create($this->validStudentData());

        $this->put("/students/{$student->id}", [...$this->validStudentData(), 'name' => 'Ada King'])
            ->assertRedirect("/students/{$student->id}")
            ->assertSessionHas('success', 'Student updated successfully!');

        $this->assertSame('Ada King', $student->fresh()->name);
    }

    public function test_update_rejects_email_of_another_student(): void
    {
        Student::create([...$this->validStudentData(), 'email' => 'taken@example.com']);
        $student = Student::create($this->validStudentData());

        $this->put("/students/{$student->id}", [...$this->validStudentData(), 'email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_student_can_be_deleted(): void
    {
        $student = Student::create($this->validStudentData());

        $this->delete("/students/{$student->id}")
            ->assertRedirect('/students')
            ->assertSessionHas('success', 'Student deleted successfully!');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}

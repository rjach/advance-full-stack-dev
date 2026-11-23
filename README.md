# Training Institute Management System

Coursework for Advanced Full Stack Development. Each workshop has its own folder, and each one is a complete Laravel app that you can run on its own. Every workshop starts from a copy of the one before it.

| Folder | Topic | Status |
| --- | --- | --- |
| [`workshop1/`](workshop1) | CRUD for Students and Courses with MySQL | Done |
| [`workshop2/`](workshop2) | MVC and DRY refactor: controllers, resource routes, Blade components | Done |
| [`workshop3/`](workshop3) | REST API for Students and Courses: Form Requests, API Resources | Done |

**Stack:** PHP 8.3+, Laravel 13, MySQL. The tests run on in-memory SQLite.

---

## Workshop 1: CRUD

The first working version of the app. Staff can manage two kinds of records:

- **Students:** name, email, phone, address, date of birth
- **Courses:** name, description, duration (weeks), fee, difficulty (`easy` / `medium` / `hard`), active flag

For each one you can list, create, view, edit and delete. Form input is validated, and errors and old input are shown again after a failed submit. Data is stored in MySQL through Eloquent models and migrations.

**How it is built:** every request is handled by a closure in `routes/web.php`. That file holds the routing, validation and queries. Each view is a full HTML page, and links are hard-coded URLs such as `/students/create`. It works, but the same code is repeated in many places, and that is what Workshop 2 fixes.

Key files:

```
workshop1/
├── routes/web.php                   # all routes, validation and queries
├── app/Models/{Student,Course}.php
├── database/migrations/*_create_{students,courses}_table.php
├── resources/views/{student,course}/{list,create,detail,edit}.blade.php
└── tests/Feature/{Student,Course}CrudTest.php
```

---

## Workshop 2: MVC and DRY refactor

The app behaves the same as in Workshop 1, but the code is reorganised so that each piece lives in the layer it belongs to (MVC) and is written only once (DRY). It also gets a shared navbar, a footer and a stylesheet.

| Week 1 problem | Week 2 fix |
| --- | --- |
| All logic in `routes/web.php` | `StudentController` and `CourseController`, each with the 7 resource methods |
| 14 hand-written routes | `Route::resource('students', …)` and `Route::resource('courses', …)` |
| Hard-coded URLs | Named routes everywhere: `route('students.show', $student)` |
| `findOrFail($id)` in every method | Route model binding: `show(Student $student)` |
| Rules repeated in store and update | One private `rules()` method per controller |
| `<!DOCTYPE>`, head and alert block in every view | `<x-layouts.app>` layout component with `<x-navbar />`, `<x-footer />` and `<x-alert />` |
| Create and edit forms nearly identical | Shared `<x-students.form>` and `<x-courses.form>` components |
| Delete form copied into each list | `<x-delete-form :action="…" />` |

Other changes:

- `Student` casts `date_of_birth` to a date and has an `age` accessor, which is shown on the details page.
- `Course` casts `fee` to 2 decimals. `Course::DIFFICULTIES` is used by both the validation rule and the select options.
- A student's email must be unique on create. On update, the student's own email is ignored by the unique check.

Key files:

```
workshop2/
├── routes/web.php                   # welcome route + 2 resource routes
├── app/Http/Controllers/{Student,Course}Controller.php
├── app/Models/{Student,Course}.php
├── public/css/app.css
└── resources/views/
    ├── components/
    │   ├── layouts/app.blade.php    # page shell
    │   ├── navbar.blade.php, footer.blade.php, alert.blade.php
    │   ├── form-input.blade.php, form-textarea.blade.php, delete-form.blade.php
    │   ├── students/form.blade.php
    │   └── courses/form.blade.php
    ├── student/{list,create,detail,edit}.blade.php
    └── course/{list,create,detail,edit}.blade.php
```

Request flow:

```
Browser → Route → Controller (validates) → Model → MySQL → Model → Controller → View (layout + components) → Browser
```

---

## Workshop 3: REST API

The backend now also serves a mobile app. The web app from Workshop 2 is unchanged, and a JSON API is added next to it under `/api`.

| Method | URL | Success | Failure |
| --- | --- | --- | --- |
| GET | `/api/students`, `/api/courses` | 200 list in `data` | |
| POST | `/api/students`, `/api/courses` | 201 created resource | 422 validation errors |
| GET | `/api/students/{id}`, `/api/courses/{id}` | 200 resource | 404 |
| PUT/PATCH | `/api/students/{id}`, `/api/courses/{id}` | 200 updated resource | 404, 422 |
| DELETE | `/api/students/{id}`, `/api/courses/{id}` | 204 no content | 404 |

- `php artisan install:api` adds Sanctum and `routes/api.php`.
- `Api\StudentController` and `Api\CourseController` are separate from the web controllers and registered with `Route::apiResource()`.
- Validation lives in Form Requests (`Store/UpdateStudentRequest`, `Store/UpdateCourseRequest`). The web controllers use the same classes, so the rules still exist only once.
- `StudentResource` and `CourseResource` map the models to JSON. Student output includes the computed `age`.
- API route names are prefixed with `api.` (for example `api.students.index`). Without the prefix they would overwrite the web route names used by `route()` in the Blade views.
- Send `Accept: application/json` so errors come back as JSON.

Key files:

```
workshop3/
├── routes/api.php
├── app/Http/Controllers/Api/{Student,Course}Controller.php
├── app/Http/Requests/{Student,Course}/{Store,Update}*Request.php
├── app/Http/Resources/{Student,Course}Resource.php
└── tests/Feature/{Student,Course}ApiTest.php
```

---

## Running a workshop

Every workshop is set up the same way. Replace `workshopN` with the folder you want.

```bash
cd workshopN
composer install
cp .env.example .env
php artisan key:generate
```

Set the MySQL connection in `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), create the database, and then run:

```bash
php artisan migrate
php artisan serve
```

Open <http://127.0.0.1:8000/students> or <http://127.0.0.1:8000/courses>. In Workshop 3 the API is at <http://127.0.0.1:8000/api/students> and <http://127.0.0.1:8000/api/courses>.

Useful commands:

```bash
php artisan test                        # feature tests (in-memory SQLite, MySQL not needed)
php artisan route:list --except-vendor  # registered routes
php artisan optimize:clear              # clear cached views and config
```

## Branches

Each workshop is built on its own branch (`workshop1`, `workshop2`, …) and merged into `main` through a pull request.

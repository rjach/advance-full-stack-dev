<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Names are prefixed with "api." so they don't overwrite the web routes
// (students.index, courses.index, ...) used by route() in the Blade views.
Route::apiResource('students', StudentController::class)->names('api.students');
Route::apiResource('courses', CourseController::class)->names('api.courses');

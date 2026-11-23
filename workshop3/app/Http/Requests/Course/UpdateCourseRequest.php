<?php

namespace App\Http\Requests\Course;

/**
 * Course update uses the same rules as create (no unique fields to ignore).
 * It is a separate class so the two can diverge without touching controllers.
 */
class UpdateCourseRequest extends StoreCourseRequest {}

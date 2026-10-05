<!DOCTYPE html>
<html>
<head>
    <title>Create Course</title>
</head>
<body>
    <h1>Create Course</h1>

    @if($errors->any())
        <div>
            <h3>Please fix the following errors:</h3>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/courses" method="POST">
        @csrf

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>
        <br>

        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <br>

        <div>
            <label for="duration">Duration (weeks)</label>
            <input type="number" id="duration" name="duration" min="1" value="{{ old('duration') }}">
        </div>
        <br>

        <div>
            <label for="fee">Fee</label>
            <input type="number" id="fee" name="fee" min="0" step="0.01" value="{{ old('fee') }}">
        </div>
        <br>

        <div>
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty">
                <option value="">-- Select --</option>
                @foreach(['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('difficulty') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <br>

        <div>
            {{-- Unchecked checkboxes are not submitted, so the hidden input sends 0 by default --}}
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
            <label for="is_active">Active</label>
        </div>
        <br>

        <button type="submit">Create Course</button>
    </form>

    <br>
    <a href="/courses">Back to Courses</a>
</body>
</html>

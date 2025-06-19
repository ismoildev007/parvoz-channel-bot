<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talaba ma'lumotlarini tahrirlash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Talaba ma'lumotlarini tahrirlash</h1>
    <a href="{{ route('students.index') }}" class="btn btn-secondary mb-3">Orqaga</a>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="first_name" class="form-label">Ism</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" readonly>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Familiya</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" readonly>
        </div>
        <div class="mb-3">
            <label for="mentor_name" class="form-label">Mentor (ixtiyoriy)</label>
            <input type="text" name="mentor_name" id="mentor_name" class="form-control" value="{{ old('mentor_name', $student->mentor_name) }}" readonly>
        </div>
        <div class="mb-3">
            <label for="mentor_name" class="form-label">Ovozlar soni</label>
            <input type="text" name="votes" id="votes" class="form-control" value="{{ old('votes', $student->votes) }}">
        </div>
        <button type="submit" class="btn btn-primary">Yangilash</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

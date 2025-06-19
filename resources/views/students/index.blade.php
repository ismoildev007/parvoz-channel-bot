<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studentlar ro'yxati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Studentlar ro'yxati</h1>
    <a href="{{ route('students.create') }}" class="btn btn-primary mb-3">Yangi student qo‘shish</a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Ism</th>
            <th>Familiya</th>
            <th>Mentor</th>
            <th>Ovozlar</th>
            <th>Amallar</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->mentor_name ?? 'Yo‘q' }}</td>
                <td>{{ $student->votes }}</td>
                <td class="p-2">
                    <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('O‘chirishni xohlaysizmi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">O‘chirish</button>
                        <a href="{{ route('students.edit', $student) }}" class="text-red-600 hover:underline btn">O'zgartirish</a>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Studentlar mavjud emas.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

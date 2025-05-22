<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Konkurslar</h1>

    <!-- Create Contest Form -->
    <form action="{{ route('admin.contest.create') }}" method="POST" class="mb-8">
        @csrf
        <div class="grid grid-cols-1 gap-4">
            <input type="text" name="name" placeholder="Konkurs nomi" class="border p-2" required>
            <textarea name="description" placeholder="Konkurs shartlari" class="border p-2"></textarea>
            <input type="datetime-local" name="start_date" class="border p-2" required>
            <input type="datetime-local" name="end_date" class="border p-2" required>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Konkurs yaratish</button>
        </div>
    </form>

    <!-- Contests List -->
    <div>
        @foreach ($contests as $contest)
            <div class="bg-white p-4 mb-4 rounded shadow">
                <h2 class="text-xl font-bold">{{ $contest->name }}</h2>
                <p>{{ $contest->description }}</p>
                <p>Boshlanish: {{ $contest->start_date }}</p>
                <p>Tugash: {{ $contest->end_date }}</p>
                <p>Holati: {{ $contest->status }}</p>
                <div class="mt-2">
                    <a href="{{ route('admin.contest.edit', $contest->id) }}" class="text-blue-500">Tahrirlash</a>
                    <a href="{{ route('students.index') }}" class="text-green-500 ml-4">studentlar</a>
{{--                    <a href="{{ route('admin.winners', $contest->id) }}" class="text-purple-500 ml-4">G'oliblar</a>--}}
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>

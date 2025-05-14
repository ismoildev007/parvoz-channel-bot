<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Konkurs Admin Paneli</h1>

    <!-- Kanallar -->
    <div class="mb-4">
        <h2 class="text-xl">Kanallar</h2>
        <form action="{{ route('admin.addChannel') }}" method="POST" class="mb-4">
            @csrf
            <input type="text" name="channel_id" placeholder="Kanal ID (@channel)" class="border p-2">
            <input type="text" name="name" placeholder="Kanal nomi" class="border p-2">
            <button type="submit" class="bg-blue-500 text-white p-2">Qo‘shish</button>
        </form>
        <ul>
            @foreach($channels as $channel)
                <li>{{ $channel->name }} <a href="{{ route('admin.deleteChannel', $channel->id) }}" class="text-red-500">O‘chirish</a></li>
            @endforeach
        </ul>
    </div>

    <!-- Konkurs sozlamalari -->
    <div class="mb-4">
        <h2 class="text-xl">Konkurs Sozlamalari</h2>
        <form action="{{ route('admin.updateContestSettings') }}" method="POST">
            @csrf
            <input type="text" name="title" value="{{ $settings->title ?? '' }}" placeholder="Konkurs nomi" class="border p-2">
            <textarea name="description" placeholder="Konkurs shartlari" class="border p-2">{{ $settings->description ?? '' }}</textarea>
            <input type="datetime-local" name="end_date" value="{{ $settings->end_date ?? '' }}" class="border p-2">
            <button type="submit" class="bg-blue-500 text-white p-2">Saqlash</button>
        </form>
    </div>

    <!-- Statistika -->
    <div>
        <h2 class="text-xl">Statistika</h2>
        <table class="w-full border">
            <thead>
            <tr>
                <th class="border p-2">Ism</th>
                <th class="border p-2">Ball</th>
                <th class="border p-2">Qo'shganlar</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="border p-2">{{ $user->first_name }}</td>
                    <td class="border p-2">{{ $user->points }}</td>
                    <td class="border p-2">{{ $user->referrals->count() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

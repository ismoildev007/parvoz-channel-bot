<!DOCTYPE html>
<html>
<head>
    <title>Konkursni tahrirlash</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ $contest->name }}</h1>

    <!-- Update Contest Form -->
    <form action="{{ route('admin.contest.update', $contest->id) }}" method="POST" class="mb-8">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4">
            <input type="text" name="name" value="{{ $contest->name }}" placeholder="Konkurs nomi" class="border p-2" required>
            <textarea name="description" placeholder="Konkurs shartlari" class="border p-2">{{ $contest->description }}</textarea>
            <input type="datetime-local" name="start_date" value="{{ \Carbon\Carbon::parse($contest->start_date)->format('Y-m-d\TH:i') }}" class="border p-2" required>
            <input type="datetime-local" name="end_date" value="{{ \Carbon\Carbon::parse($contest->end_date)->format('Y-m-d\TH:i') }}" class="border p-2" required>
            <select name="status" class="border p-2">
                <option value="active" {{ $contest->status == 'active' ? 'selected' : '' }}>Faol</option>
                <option value="finished" {{ $contest->status == 'finished' ? 'selected' : '' }}>Tugallangan</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Yangilash</button>
        </div>
    </form>

    <!-- Add Channel Form -->
    <form action="{{ route('admin.channel.add', $contest->id) }}" method="POST" class="mb-8">
        @csrf
        <div class="grid grid-cols-1 gap-4">
            <input type="text" name="telegram_id" placeholder="Kanal Telegram ID" class="border p-2" required>
            <input type="text" name="name" placeholder="Kanal nomi" class="border p-2" required>
            <input type="url" name="invite_link" placeholder="Kanal havolasi" class="border p-2" required>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Kanal qo‘shish</button>
        </div>
    </form>

    <!-- Channels List -->
    <h2 class="text-xl font-bold mb-2">Kanallar</h2>
    <ul>
        @foreach ($contest->channels as $channel)
            <li class="mb-2">
                {{ $channel->name }} ({{ $channel->telegram_id }})
                <form action="{{ route('admin.channel.remove', [$contest->id, $channel->id]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500">O‘chirish</button>
                </form>
            </li>
        @endforeach
    </ul>

    <!-- Add Prize Form -->
    <form action="{{ route('admin.prize.add', $contest->id) }}" method="POST" class="mb-8">
        @csrf
        <div class="grid grid-cols-1 gap-4">
            <input type="text" name="name" placeholder="Sovrin nomi" class="border p-2" required>
            <input type="number" name="position" placeholder="O‘rin (1, 2, 3...)" class="border p-2" required>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Sovrin qo‘shish</button>
        </div>
    </form>

    <!-- Prizes List -->
    <h2 class="text-xl font-bold mb-2">Sovrinlar</h2>
    <ul>
        @foreach ($contest->prizes as $prize)
            <li class="mb-2">
                {{ $prize->position }}-o‘rin: {{ $prize->name }}
                <form action="{{ route('admin.prize.remove', [$contest->id, $prize->id]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500">O‘chirish</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
</body>
</html>

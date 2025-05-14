<!DOCTYPE html>
<html>
<head>
    <title>G‘oliblar</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ $contest->name }} - G‘oliblar</h1>

    <ul>
        @foreach ($winners as $index => $winner)
            <li class="mb-2">
                {{ $index + 1 }}-o‘rin: {{ $winner->first_name }} ({{ $winner->points }} ball, {{ $winner->referrals_count }} referal)
                @if (isset($prizes[$index]))
                    - Sovrin: {{ $prizes[$index]->name }}
                @endif
            </li>
        @endforeach
    </ul>
</div>
</body>
</html>

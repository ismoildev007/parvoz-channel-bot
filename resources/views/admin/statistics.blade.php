<!DOCTYPE html>
<html>
<head>
    <title>Statistika</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ $contest->name }} - Statistika</h1>

    <h2 class="text-xl font-bold mb-2">Top 10 ishtirokchi</h2>
    <table class="w-full border">
        <thead>
        <tr class="bg-gray-200">
            <th class="border p-2">Ism</th>
            <th class="border p-2">Ball</th>
            <th class="border p-2">Referallar soni</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($leaderboard as $user)
            <tr>
                <td class="border p-2">{{ $user->first_name }}</td>
                <td class="border p-2">{{ $user->points }}</td>
                <td class="border p-2">{{ $user->referrals_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battleships - Leaderboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-50">
    <div class="container mx-auto p-8 pb-24">
        <header class="flex justify-between items-center mb-12">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">
                Battleships Leaderboard
            </h1>
            <a href="/" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-100 rounded-lg font-medium transition-all border border-zinc-700/50">
                Back to Game
            </a>
        </header>

        <div class="mb-8">
            <div class="flex gap-4">
                <a href="{{ route('leaderboard', ['difficulty' => 'easy']) }}" 
                   class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-100 rounded-lg font-medium transition-all border border-zinc-700/50 {{ $difficulty === 'easy' ? 'bg-zinc-700' : '' }}">
                    Easy
                </a>
                <a href="{{ route('leaderboard', ['difficulty' => 'medium']) }}"
                   class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-100 rounded-lg font-medium transition-all border border-zinc-700/50 {{ $difficulty === 'medium' ? 'bg-zinc-700' : '' }}">
                    Medium
                </a>
                <a href="{{ route('leaderboard', ['difficulty' => 'hard']) }}"
                   class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-100 rounded-lg font-medium transition-all border border-zinc-700/50 {{ $difficulty === 'hard' ? 'bg-zinc-700' : '' }}">
                    Hard
                </a>
            </div>
        </div>

        <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
            <table class="w-full">
                <thead class="bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-zinc-400 font-medium">Rank</th>
                        <th class="px-6 py-3 text-left text-zinc-400 font-medium">Player</th>
                        <th class="px-6 py-3 text-right text-zinc-400 font-medium">Wins</th>
                        <th class="px-6 py-3 text-right text-zinc-400 font-medium">Losses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach($leaderboards as $index => $entry)
                        <tr class="hover:bg-zinc-800/50">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">{{ $entry->user->name }}</td>
                            <td class="px-6 py-4 text-right text-green-500">{{ $entry->wins }}</td>
                            <td class="px-6 py-4 text-right text-red-500">{{ $entry->losses }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <footer class="fixed bottom-0 left-0 right-0 bg-zinc-900/80 backdrop-blur-sm py-4">
        <div class="container mx-auto px-8 flex justify-between items-center">
            <p class="text-zinc-400">Created by:</p>
            <div class="flex items-center gap-4">
                <a href="https://github.com/conjurs" target="_blank" class="flex items-center gap-2 text-zinc-300 hover:text-blue-500 transition-colors">
                    <img src="https://github.com/conjurs.png" alt="conjurs" class="w-8 h-8 rounded-full">
                    @conjurs
                </a>
                <a href="https://github.com/Joosepi" target="_blank" class="flex items-center gap-2 text-zinc-300 hover:text-blue-500 transition-colors">
                    <img src="https://github.com/Joosepi.png" alt="Joosepi" class="w-8 h-8 rounded-full">
                    @Joosepi
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
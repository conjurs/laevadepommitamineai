<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battleships vs AI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-50">
    <div class="container mx-auto p-8">
        <header class="flex justify-between items-center mb-12">
            <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-500 to-purple-600 bg-clip-text text-transparent">
                Battleships vs AI
            </h1>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 bg-zinc-900 px-4 py-2 rounded-lg">
                    <span class="text-zinc-400">Score:</span>
                    <span id="score" class="text-2xl font-bold text-blue-500">0</span>
                </div>
                
                <select id="difficulty" class="bg-zinc-900 border-2 border-zinc-800 text-zinc-300 rounded-lg h-11 px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition-colors">
                    <option value="easy" class="bg-zinc-900">Easy</option>
                    <option value="medium" selected class="bg-zinc-900">Medium</option>
                    <option value="hard" class="bg-zinc-900">Hard</option>
                </select>

                <button id="start-game" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    Start Game
                </button>

                <button id="reset-game" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    Reset Game
                </button>
            </div>
        </header>

        <div id="ship-preview" class="mb-8 bg-zinc-900 rounded-xl border border-zinc-800 p-6">
            <h3 class="text-xl font-semibold mb-4">Current Ship: <span id="current-ship-name" class="text-blue-500"></span></h3>
            <div id="preview-grid" class="flex gap-1 mb-4"></div>
            <div class="flex items-center gap-4">
                <p class="text-zinc-400">Orientation: <span id="orientation-display" class="text-zinc-200">Horizontal</span></p>
                <button id="rotate-ship" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg transition-colors">
                    Rotate Ship
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-16 max-w-6xl mx-auto">
            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <h2 class="text-xl font-semibold">Your Fleet</h2>
                </div>
                <div class="relative">
                    <div class="absolute -left-10 top-0 bottom-0 flex flex-col justify-around text-zinc-500 text-sm">
                        @foreach(range('A', 'J') as $letter)
                            <div>{{ $letter }}</div>
                        @endforeach
                    </div>
                    <div class="absolute -top-6 left-0 right-0 flex justify-around text-zinc-500 text-sm">
                        @foreach(range(1, 10) as $number)
                            <div>{{ $number }}</div>
                        @endforeach
                    </div>
                    <div id="player-board" class="grid grid-cols-10 bg-zinc-900 p-2 rounded-lg border border-zinc-800">
                        @for ($i = 0; $i < 10; $i++)
                            @for ($j = 0; $j < 10; $j++)
                                <div data-x="{{ $i }}" data-y="{{ $j }}" 
                                     class="w-10 h-10 rounded bg-zinc-800 hover:bg-zinc-700 transition-colors border border-zinc-900">
                                </div>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <h2 class="text-xl font-semibold">Enemy Fleet</h2>
                </div>
                <div class="relative">
                    <div class="absolute -left-10 top-0 bottom-0 flex flex-col justify-around text-zinc-500 text-sm">
                        @foreach(range('A', 'J') as $letter)
                            <div>{{ $letter }}</div>
                        @endforeach
                    </div>
                    <div class="absolute -top-6 left-0 right-0 flex justify-around text-zinc-500 text-sm">
                        @foreach(range(1, 10) as $number)
                            <div>{{ $number }}</div>
                        @endforeach
                    </div>
                    <div id="ai-board" class="grid grid-cols-10 bg-zinc-900 p-2 rounded-lg border border-zinc-800">
                        @for ($i = 0; $i < 10; $i++)
                            @for ($j = 0; $j < 10; $j++)
                                <button data-x="{{ $i }}" data-y="{{ $j }}" disabled 
                                        class="w-10 h-10 rounded bg-zinc-800 hover:bg-zinc-700 transition-colors border border-zinc-900">
                                </button>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf({
                duration: 2000,
                position: { x: 'right', y: 'top' }
            });

            let gamePhase = 'setup';
            let isHorizontal = true;
            let currentShipSize = 0;
            let lastClickTime = 0;
            const COOLDOWN_TIME = 1000;

            function updatePreviewGrid(size) {
                const previewGrid = document.getElementById('preview-grid');
                previewGrid.innerHTML = '';
                previewGrid.style.display = 'flex';
                previewGrid.style.flexDirection = isHorizontal ? 'row' : 'column';
                
                for (let i = 0; i < size; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'w-10 h-10 bg-blue-500 rounded border border-blue-600';
                    previewGrid.appendChild(cell);
                }
            }

            document.getElementById('start-game').addEventListener('click', async () => {
                try {
                    const difficulty = document.getElementById('difficulty').value;
                    const response = await axios.post('/game/start', { difficulty });
                    
                    if (response.data.status === 'success') {
                        gamePhase = 'placement';
                        currentShipSize = response.data.shipSize;
                        document.getElementById('current-ship-name').textContent = response.data.shipName;
                        updatePreviewGrid(currentShipSize);
                        notyf.success(response.data.message);
                    }
                } catch (error) {
                    notyf.error('Error starting game');
                    console.error(error);
                }
            });

            document.getElementById('rotate-ship').addEventListener('click', () => {
                isHorizontal = !isHorizontal;
                document.getElementById('orientation-display').textContent = isHorizontal ? 'Horizontal' : 'Vertical';
                updatePreviewGrid(currentShipSize);
            });

            document.getElementById('player-board').addEventListener('click', async (e) => {
                if (gamePhase !== 'placement' || !e.target.matches('div')) return;

                const now = Date.now();
                if (now - lastClickTime < COOLDOWN_TIME) {
                    notyf.error('Please wait before placing another ship');
                    return;
                }
                lastClickTime = now;

                const x = parseInt(e.target.dataset.x);
                const y = parseInt(e.target.dataset.y);

                try {
                    const response = await axios.post('/game/place-ship', {
                        x,
                        y,
                        horizontal: isHorizontal
                    });

                    if (response.data.status === 'success') {
                        for (let i = 0; i < currentShipSize; i++) {
                            const cellX = isHorizontal ? x : x + i;
                            const cellY = isHorizontal ? y + i : y;
                            const cell = document.querySelector(`div[data-x="${cellX}"][data-y="${cellY}"]`);
                            if (cell) {
                                cell.classList.remove('bg-zinc-800');
                                cell.classList.add('bg-blue-500');
                            }
                        }

                        if (response.data.phase === 'playing') {
                            gamePhase = 'playing';
                            document.getElementById('ship-preview').style.display = 'none';
                            notyf.success('All ships placed! Game starting...');
                            document.querySelectorAll('#ai-board button').forEach(btn => btn.disabled = false);
                        } else {
                            currentShipSize = response.data.nextShipSize;
                            document.getElementById('current-ship-name').textContent = response.data.nextShipName;
                            updatePreviewGrid(currentShipSize);
                            notyf.success(response.data.message);
                        }
                    }
                } catch (error) {
                    notyf.error(error.response?.data?.message || 'Invalid ship placement');
                }
            });

            document.getElementById('reset-game').addEventListener('click', async () => {
                try {
                    await axios.post('/game/reset');
                    location.reload();
                } catch (error) {
                    notyf.error('Error resetting game');
                }
            });

            document.getElementById('ai-board').addEventListener('click', async (e) => {
                if (gamePhase !== 'playing' || !e.target.matches('button') || e.target.disabled) return;

                const x = parseInt(e.target.dataset.x);
                const y = parseInt(e.target.dataset.y);

                try {
                    const response = await axios.post('/game/shoot', { x, y });
                    
                    // Update the attacked cell
                    e.target.disabled = true;
                    e.target.classList.remove('bg-zinc-800');
                    e.target.classList.add(response.data.hit ? 'bg-red-500' : 'bg-gray-500');

                    // Update score if hit
                    if (response.data.hit) {
                        const scoreElement = document.getElementById('score');
                        scoreElement.textContent = parseInt(scoreElement.textContent) + 1;
                    }

                    // Handle AI's turn
                    if (response.data.aiShot) {
                        const playerCell = document.querySelector(
                            `div[data-x="${response.data.aiShot.x}"][data-y="${response.data.aiShot.y}"]`
                        );
                        if (playerCell) {
                            playerCell.classList.remove('bg-zinc-800', 'bg-blue-500');
                            playerCell.classList.add(response.data.aiShot.hit ? 'bg-red-500' : 'bg-gray-500');
                        }
                    }

                    if (response.data.gameOver) {
                        gamePhase = 'ended';
                        notyf.success(`Game Over! ${response.data.winner} wins!`);
                    }
                } catch (error) {
                    notyf.error('Error processing shot');
                }
            });
        });
    </script>
</body>
</html> 
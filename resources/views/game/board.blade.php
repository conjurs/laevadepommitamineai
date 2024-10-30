<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Battleships vs AI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-50 overflow-x-hidden">
    <div class="matrix-bg"></div>
    
    <div id="gameOverModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-zinc-900 p-8 rounded-xl shadow-xl text-center max-w-md w-full mx-4">
            <h2 class="text-4xl font-bold mb-4 rainbow-text">Game Over!</h2>
            <p id="winnerText" class="text-2xl mb-6 text-zinc-200"></p>
            <p id="finalScore" class="text-xl mb-6 text-blue-500"></p>
            <button onclick="window.resetGame()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                Replay
            </button>
        </div>
    </div>

    <!-- Main game content -->
    <div class="container mx-auto p-8">
        <header class="flex justify-between items-center mb-12">
            <div class="title-container">
                <h1 class="title-animate">
                    Battleships vs AI
                </h1>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 bg-zinc-900/50 backdrop-blur px-6 py-3 rounded-lg border border-zinc-700/50 shadow-lg">
                    <span class="text-zinc-400 font-medium">Score:</span>
                    <span id="score" class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">0</span>
                </div>
                
                <select id="difficulty" class="difficulty-select">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                </select>

                <button id="start-game" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all hover:shadow-lg hover:shadow-blue-500/20">
                    Start Game
                </button>

                <button id="reset-game" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-all hover:shadow-lg hover:shadow-red-500/20">
                    Reset Game
                </button>
            </div>
        </header>

        <div id="ship-preview" class="mb-8 bg-zinc-900 rounded-xl border border-zinc-800 p-6">
            <h3 class="text-xl font-semibold mb-4">Current Ship: <span id="current-ship-name" class="text-blue-500"></span></h3>
            <div id="preview-grid" class="flex gap-1 mb-4"></div>
            <div class="flex items-center gap-4">
                <p class="text-zinc-400">Orientation: <span id="orientation-display" class="text-zinc-200">Horizontal</span></p>
                <button id="rotate-ship" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Rotate Ship
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 max-w-6xl mx-auto">
            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <h2 class="text-xl font-semibold">Your Fleet</h2>
                </div>
                <div class="relative">
                    <div class="absolute -left-8 top-0 bottom-0 flex flex-col justify-between text-zinc-500 text-sm h-full">
                        @foreach(range('A', 'J') as $letter)
                            <div class="h-12 flex items-center justify-end pr-2">{{ $letter }}</div>
                        @endforeach
                    </div>
                    <div class="absolute -top-8 left-0 right-0 flex text-zinc-500 text-sm w-full">
                        @foreach(range('A', 'J') as $index => $letter)
                            <div class="w-12 flex items-center justify-center">{{ $index + 1 }}</div>
                        @endforeach
                    </div>
                    <div id="player-board">
                        @for ($i = 0; $i < 10; $i++)
                            @for ($j = 0; $j < 10; $j++)
                                <div data-x="{{ $i }}" data-y="{{ $j }}" 
                                     class="w-12 h-12 rounded bg-zinc-800 hover:bg-zinc-700 transition-colors border border-zinc-900">
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
                    <div class="absolute -left-8 top-0 bottom-0 flex flex-col justify-between text-zinc-500 text-sm h-full">
                        @foreach(range('A', 'J') as $letter)
                            <div class="h-12 flex items-center justify-end pr-2">{{ $letter }}</div>
                        @endforeach
                    </div>
                    <div class="absolute -top-8 left-0 right-0 flex text-zinc-500 text-sm w-full">
                        @foreach(range('A', 'J') as $index => $letter)
                            <div class="w-12 flex items-center justify-center">{{ $index + 1 }}</div>
                        @endforeach
                    </div>
                    <div id="ai-board">
                        @for ($i = 0; $i < 10; $i++)
                            @for ($j = 0; $j < 10; $j++)
                                <button data-x="{{ $i }}" data-y="{{ $j }}" disabled 
                                        class="w-12 h-12 rounded bg-zinc-800 hover:bg-zinc-700 transition-colors border border-zinc-900">
                                </button>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto mt-8">
            <div class="bg-zinc-900 rounded-lg border border-zinc-800">
                <div class="px-4 py-3 border-b border-zinc-800">
                    <h3 class="text-lg font-semibold text-zinc-100">Battle Log</h3>
                </div>
                <div id="game-log" class="h-48 overflow-y-auto">
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Creator credits -->
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
                        addLogMessage(response.data.message, 'success');
                    }
                } catch (error) {
                    addLogMessage('Error starting game', 'error');
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
                                cell.classList.remove('bg-zinc-800', 'bg-zinc-700');
                                cell.classList.add('bg-blue-500');
                                cell.style.transition = 'background-color 0.3s ease';
                            }
                        }

                        if (response.data.phase === 'playing') {
                            gamePhase = 'playing';
                            document.getElementById('ship-preview').style.display = 'none';
                            addLogMessage('All ships placed! Game starting...', 'success');
                            document.querySelectorAll('#ai-board button').forEach(btn => btn.disabled = false);
                        } else {
                            currentShipSize = response.data.nextShipSize;
                            document.getElementById('current-ship-name').textContent = response.data.nextShipName;
                            updatePreviewGrid(currentShipSize);
                            addLogMessage(response.data.message, 'success');
                        }
                    }
                } catch (error) {
                    addLogMessage('Invalid ship placement', 'error');
                }
            });

            document.getElementById('reset-game').addEventListener('click', async () => {
                try {
                    axios.post('/game/reset').then(() => {
                        gamePhase = 'setup';
                        currentShipSize = 0;
                        isHorizontal = true;
                        document.getElementById('score').textContent = '0';
                        document.getElementById('ship-preview').style.display = 'block';
                        document.getElementById('gameOverModal').classList.add('hidden');
                        
                        // Reset all board cells
                        document.querySelectorAll('#player-board div').forEach(cell => {
                            cell.classList.remove('bg-blue-500', 'bg-red-500', 'bg-gray-500');
                            cell.classList.add('bg-zinc-800');
                        });
                        
                        document.querySelectorAll('#ai-board button').forEach(btn => {
                            btn.classList.remove('bg-red-500', 'bg-gray-500');
                            btn.classList.add('bg-zinc-800');
                            btn.disabled = true;
                        });
                        
                        location.reload();
                    });
                } catch (error) {
                    addLogMessage('Error resetting game', 'error');
                }
            });

            document.getElementById('ai-board').addEventListener('click', async (e) => {
                if (gamePhase !== 'playing' || !e.target.matches('button') || e.target.disabled) return;

                const x = parseInt(e.target.dataset.x);
                const y = parseInt(e.target.dataset.y);

                try {
                    const response = await axios.post('/game/shoot', { x, y });
                    
                    e.target.disabled = true;
                    e.target.classList.remove('bg-zinc-800', 'bg-zinc-700', 'hover:bg-zinc-700');
                    
                    // Add a small delay to ensure proper transition
                    setTimeout(() => {
                        e.target.classList.add(response.data.hit ? 'bg-red-500' : 'bg-gray-500');
                    }, 50);
                    
                    addLogMessage(response.data.hit ? 'You hit a ship!' : 'Miss!', 'success');

                    if (response.data.hit) {
                        const scoreElement = document.getElementById('score');
                        scoreElement.textContent = parseInt(scoreElement.textContent) + 1;
                    }

                    if (response.data.ai_shot) {
                        const aiX = response.data.ai_shot.x;
                        const aiY = response.data.ai_shot.y;
                        const playerCell = document.querySelector(
                            `div[data-x="${aiX}"][data-y="${aiY}"]`
                        );
                        
                        if (playerCell) {
                            playerCell.classList.remove('bg-zinc-800', 'bg-blue-500');
                            playerCell.classList.add(response.data.ai_hit ? 'bg-red-500' : 'bg-gray-500');
                            playerCell.style.transition = 'background-color 0.3s ease';
                            
                            addLogMessage(
                                response.data.ai_hit ? 
                                `AI hits your ship at ${String.fromCharCode(65 + aiX)}${aiY + 1}!` : 
                                `AI misses at ${String.fromCharCode(65 + aiX)}${aiY + 1}`,
                                'error'
                            );
                        }
                    }

                    if (response.data.gameOver) {
                        gamePhase = 'ended';
                        const score = document.getElementById('score').textContent;
                        showGameOver(response.data.winner, score);
                        document.querySelectorAll('#ai-board button').forEach(btn => btn.disabled = true);
                        
                        const gameOverMessage = response.data.winner === 'Player' ? 
                            'Congratulations! You Win!' : 
                            'Game Over - AI Wins!';
                        addLogMessage(gameOverMessage, 'success');
                    }

                } catch (error) {
                    addLogMessage('Error processing shot', 'error');
                }
            });

            function showGameOver(winner, score) {
                const modal = document.getElementById('gameOverModal');
                const winnerText = document.getElementById('winnerText');
                const finalScore = document.getElementById('finalScore');
                
                winnerText.textContent = winner === 'Player' ? 
                    'Congratulations! You Win!' : 
                    'Game Over - AI Wins!';
                
                finalScore.textContent = `Final Score: ${score}`;
                modal.classList.remove('hidden');
            }

            function resetGame() {
                try {
                    axios.post('/game/reset').then(() => {
                        gamePhase = 'setup';
                        currentShipSize = 0;
                        isHorizontal = true;
                        document.getElementById('score').textContent = '0';
                        document.getElementById('ship-preview').style.display = 'block';
                        document.getElementById('gameOverModal').classList.add('hidden');
                        
                        // Reset all board cells
                        document.querySelectorAll('#player-board div').forEach(cell => {
                            cell.classList.remove('bg-blue-500', 'bg-red-500', 'bg-gray-500');
                            cell.classList.add('bg-zinc-800');
                        });
                        
                        document.querySelectorAll('#ai-board button').forEach(btn => {
                            btn.classList.remove('bg-red-500', 'bg-gray-500');
                            btn.classList.add('bg-zinc-800');
                            btn.disabled = true;
                        });
                        
                        location.reload();
                    });
                } catch (error) {
                    addLogMessage('Error resetting game', 'error');
                }
            }

            function addLogMessage(message, type) {
                const logContainer = document.getElementById('game-log');
                const entry = document.createElement('div');
                entry.className = `log-entry ${type}`;
                entry.textContent = message;
                logContainer.prepend(entry);
                
                
                while (logContainer.children.length > 50) {
                    logContainer.removeChild(logContainer.lastChild);
                }
            }
        });
    </script>
</body>
</html> 
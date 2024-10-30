<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Battleships vs AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-4">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Battleships vs AI</h1>
            <div class="flex gap-4">
                <div class="text-xl font-semibold">Score: <span id="score">0</span></div>
                <button id="start-game" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Start Game
                </button>
                <button id="reset-game" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Reset Game
                </button>
            </div>
        </div>

        <div class="max-w-[1000px] mx-auto mb-8" id="ship-preview">
            <h3 class="text-lg font-semibold">Current Ship: <span id="current-ship-name"></span></h3>
            <div class="flex gap-1 my-2" id="preview-grid"></div>
            <div class="flex gap-4 items-center">
                <p class="text-sm text-gray-600">Orientation: <span id="orientation-display">Horizontal</span></p>
                <button id="rotate-ship" class="px-3 py-1 bg-purple-500 text-white text-sm rounded hover:bg-purple-600">
                    Rotate Ship
                </button>
            </div>
        </div>

        <!-- Game Grid -->
        <div class="grid grid-cols-2 gap-12 max-w-[1000px] mx-auto">
            <!-- Player Section -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Your Board</h2>
                    <div id="cooldown" class="hidden text-lg font-semibold text-blue-600"></div>
                </div>
                
                <!-- Player Board -->
                <div class="w-[400px] h-[400px] grid grid-cols-10 grid-rows-10" id="player-board">
                    @for ($i = 0; $i < 10; $i++)
                        @for ($j = 0; $j < 10; $j++)
                            <div 
                                class="border border-gray-300 bg-blue-200 cursor-pointer hover:bg-blue-300 transition-colors"
                                data-x="{{ $i }}"
                                data-y="{{ $j }}"
                            ></div>
                        @endfor
                    @endfor
                </div>
            </div>
            
            <!-- Enemy Section -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Enemy Board</h2>
                <div class="w-[400px] h-[400px] grid grid-cols-10 grid-rows-10" id="ai-board">
                    @for ($i = 0; $i < 10; $i++)
                        @for ($j = 0; $j < 10; $j++)
                            <button 
                                class="border border-gray-300 bg-blue-200 hover:bg-blue-300 transition-colors"
                                data-x="{{ $i }}"
                                data-y="{{ $j }}"
                                disabled
                            ></button>
                        @endfor
                    @endfor
                </div>
            </div>
        </div>

        <!-- Game Over Modal -->
        <div id="game-over-modal" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
                <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full">
                    <h2 class="text-2xl font-bold mb-4">Game Over!</h2>
                    <p class="text-xl mb-4">Winner: <span id="winner"></span></p>
                    <p class="text-lg mb-4">Final Score: <span id="final-score"></span></p>
                    <button onclick="location.reload()" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Play Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 1024px) {
            #player-board, #ai-board {
                width: 100% !important;
                height: auto !important;
                aspect-ratio: 1 / 1;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notyf = new Notyf({
                duration: 2000,
                position: { x: 'right', y: 'top' }
            });
            
            let isHorizontal = true;
            let currentShipSize = 0;
            let gamePhase = 'setup';
            
            const startButton = document.getElementById('start-game');
            const resetButton = document.getElementById('reset-game');
            const playerBoard = document.getElementById('player-board');
            const aiBoard = document.getElementById('ai-board');
            const rotateButton = document.getElementById('rotate-ship');
            const shipPreview = document.getElementById('ship-preview');
            const previewGrid = document.getElementById('preview-grid');
            
            function updatePreviewGrid(size) {
                previewGrid.innerHTML = '';
                currentShipSize = size;
                
                for (let i = 0; i < size; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'w-8 h-8 bg-blue-500 border border-gray-300';
                    previewGrid.appendChild(cell);
                }
                
                previewGrid.style.flexDirection = isHorizontal ? 'row' : 'column';
            }
            
            rotateButton.addEventListener('click', () => {
                isHorizontal = !isHorizontal;
                document.getElementById('orientation-display').textContent = isHorizontal ? 'Horizontal' : 'Vertical';
                if (currentShipSize > 0) {
                    updatePreviewGrid(currentShipSize);
                }
                notyf.success(`Orientation: ${isHorizontal ? 'Horizontal' : 'Vertical'}`);
            });
            
            startButton.addEventListener('click', async () => {
                try {
                    const response = await axios.post('/game/start');
                    startButton.disabled = true;
                    shipPreview.style.display = 'block';
                    gamePhase = 'placement';
                    
                    // Update ship preview
                    document.getElementById('current-ship-name').textContent = response.data.shipName;
                    updatePreviewGrid(response.data.shipSize);
                    
                    notyf.success(response.data.message);
                } catch (error) {
                    notyf.error('Error starting game');
                }
            });
            
            let lastClickTime = 0;
            const COOLDOWN_TIME = 1000; // 1 second cooldown

            playerBoard.addEventListener('click', async (e) => {
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
                    
                    // Place ship on board
                    const shipSize = currentShipSize;
                    for (let i = 0; i < shipSize; i++) {
                        const cellX = isHorizontal ? x : x + i;
                        const cellY = isHorizontal ? y + i : y;
                        const cell = playerBoard.querySelector(`div[data-x="${cellX}"][data-y="${cellY}"]`);
                        if (cell) {
                            cell.classList.remove('bg-blue-200', 'hover:bg-blue-300');
                            cell.classList.add('bg-blue-500');
                        }
                    }
                    
                    if (response.data.phase === 'playing') {
                        notyf.success('All ships placed! Game starting...');
                        shipPreview.style.display = 'none';
                        gamePhase = 'playing';
                        enableAIBoard();
                    } else {
                        notyf.success(`Placed ${response.data.shipSize}-square ship`);
                        updatePreviewGrid(response.data.nextShipSize);
                        document.getElementById('current-ship-name').textContent = response.data.nextShipName;
                    }
                } catch (error) {
                    notyf.error(error.response?.data?.message || 'Cannot place ship here');
                }
            });
            
            resetButton.addEventListener('click', async () => {
                try {
                    gamePhase = 'setup';
                    currentShipSize = 0;
                    isHorizontal = true;
                    
                    // Reset UI elements
                    startButton.disabled = false;
                    shipPreview.style.display = 'none';
                    document.getElementById('current-ship-name').textContent = '';
                    document.getElementById('orientation-display').textContent = 'Horizontal';
                    document.getElementById('score').textContent = '0';
                    
                    // Reset boards
                    const cells = document.querySelectorAll('#player-board div, #ai-board button');
                    cells.forEach(cell => {
                        cell.className = 'border border-gray-300 bg-blue-200 hover:bg-blue-300 transition-colors';
                        if (cell.tagName === 'BUTTON') cell.disabled = true;
                    });
                    
                    // Reset server state
                    await axios.post('/game/reset');
                    notyf.success('Game Reset');
                } catch (error) {
                    notyf.error('Error resetting game');
                }
            });
        });
    </script>
</body>
</html> 
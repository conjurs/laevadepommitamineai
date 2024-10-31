import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    let lastClickTime = 0;
    const COOLDOWN_TIME = 1500; // 1.5 seconds in milliseconds
    let cooldownTimer = null;

    document.getElementById('ai-board').addEventListener('click', async (e) => {
        const currentTime = Date.now();
        if (currentTime - lastClickTime < COOLDOWN_TIME) {
            const remainingCooldown = ((COOLDOWN_TIME - (currentTime - lastClickTime)) / 1000).toFixed(1);
            addLogMessage(`Please wait ${remainingCooldown}s before shooting again`, 'error');
            return;
        }

        const aiBoard = document.getElementById('ai-board');
        aiBoard.classList.add('cooldown');
        lastClickTime = currentTime;

        // Start cooldown timer display
        const cooldownDisplay = document.createElement('div');
        cooldownDisplay.className = 'cooldown-timer';
        document.body.appendChild(cooldownDisplay);

        const updateCooldown = () => {
            const remaining = ((COOLDOWN_TIME - (Date.now() - lastClickTime)) / 1000).toFixed(1);
            if (remaining > 0) {
                cooldownDisplay.textContent = `${remaining}s`;
                requestAnimationFrame(updateCooldown);
            } else {
                cooldownDisplay.remove();
                aiBoard.classList.remove('cooldown');
            }
        };
        updateCooldown();

        if (gamePhase !== 'playing' || !e.target.matches('button') || e.target.disabled) return;

        // Rest of your existing click handler code...
    });
});

function showGameOver(winner, score) {
    const modal = document.getElementById('gameOverModal');
    const winnerText = document.getElementById('winnerText');
    const finalScore = document.getElementById('finalScore');
    
    winnerText.textContent = `${winner} wins!`;
    finalScore.textContent = `Final Score: ${score}`;
    modal.classList.remove('hidden');
}

function resetGame() {
    try {
        document.getElementById('gameOverModal').classList.add('hidden');
        axios.post('/game/reset').then(() => {
            gamePhase = 'setup';
            currentShipSize = 0;
            isHorizontal = true;
            document.getElementById('score').textContent = '0';
            document.getElementById('ship-preview').style.display = 'block';
            
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
        notyf.error('Error resetting game');
    }
}

window.resetGame = resetGame;

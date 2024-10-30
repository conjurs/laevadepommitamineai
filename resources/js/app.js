import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
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

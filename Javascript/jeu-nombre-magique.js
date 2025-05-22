// Javascript/jeu-nombre-magique.js
let magicNumber;
let attempts;
let winsLessThan5 = parseInt(localStorage.getItem('magicGame_winsLessThan5')) || 0;
let motivatedForSpaceTrip = localStorage.getItem('magicGame_motivatedForSpaceTrip') === 'true';

const guessInput = document.getElementById('guessInput');
const messageDiv = document.getElementById('message');
const attemptsSpan = document.getElementById('attempts');
const rewardInfoDiv = document.getElementById('rewardInfo');
const guessButton = document.getElementById('guessButton');
const resetButton = document.getElementById('resetButton');

function initGame() {
    magicNumber = Math.floor(Math.random() * 50) + 1; // Range 1-50
    attempts = 0;
    messageDiv.style.display = 'none';
    messageDiv.textContent = '';
    attemptsSpan.textContent = '0';
    guessInput.value = '';
    guessInput.disabled = false;
    guessButton.disabled = false;
    updateRewardInfo();
    guessInput.focus();
}

function updateRewardInfo() {
    rewardInfoDiv.innerHTML = ''; // Clear previous messages
    if (motivatedForSpaceTrip) {
        rewardInfoDiv.innerHTML = `<p>Félicitations Caryl ! Vous avez deviné 2 fois en moins de 5 tentatives !<br>Si vous trouvez le prochain nombre en <strong>3 tentatives ou moins</strong>, vous gagnez un voyage dans l'espace !</p>`;
    } else if (winsLessThan5 === 1) {
        rewardInfoDiv.innerHTML = `<p>Bien joué Caryl ! Encore une fois en moins de 5 tentatives et un défi spécial vous attendra !</p>`;
    }
}

function makeGuess() {
    if (guessInput.disabled) return;

    const guess = parseInt(guessInput.value);

    if (isNaN(guess) || guess < 1 || guess > 50) {
        showMessage('Veuillez entrer un nombre entre 1 et 50 !', 'hint');
        return;
    }

    attempts++;
    attemptsSpan.textContent = attempts;

    if (guess === magicNumber) {
        handleWin();
    } else if (guess < magicNumber) {
        showMessage('📈 Plus grand !', 'hint');
    } else {
        showMessage('📉 Plus petit !', 'hint');
    }

    guessInput.value = '';
    guessInput.focus();
}

async function handleWin() {
    guessInput.disabled = true;
    guessButton.disabled = true;
    let winMessage = `🎉 Bravo Caryl ! Le nombre était ${magicNumber}.<br>Vous l'avez trouvé en ${attempts} tentative${attempts > 1 ? 's' : ''} !`;
    let messageType = 'success';

    // Space Trip Win Condition
    if (motivatedForSpaceTrip && attempts <= 3) {
        winMessage += `<br><br>🚀 INCROYABLE ! Vous avez réussi le défi !<br>Un voyage dans l'espace (valeur 500 000€) vous est offert !<br>Vérification de votre session...`;
        showMessage(winMessage, 'special-win'); // Special styling for big win
        
        // AJAX call to backend
        try {
            const response = await fetch('../ajax/process_magic_win.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'claim_space_trip' })
            });
            const result = await response.json();
            if (result.success) {
                appendMessage(`<br>✨ Le voyage a été ajouté à votre historique de paiements (simulé) ! Bon voyage ! ✨`);
            } else {
                appendMessage(`<br>⚠️ ${result.message || "Un problème est survenu lors de l'enregistrement du voyage."}`);
            }
        } catch (error) {
            console.error('Error processing space trip win:', error);
            appendMessage(`<br>⚠️ Erreur de communication avec le serveur pour le voyage spatial.`);
        }
        
        // Reset motivation state after attempt
        motivatedForSpaceTrip = false;
        localStorage.setItem('magicGame_motivatedForSpaceTrip', 'false');
        winsLessThan5 = 0; // Reset this counter too as the "big prize" sequence is over
        localStorage.setItem('magicGame_winsLessThan5', winsLessThan5.toString());

    } else { // Other win conditions
        if (attempts < 10) {
            winMessage += `<br>☕ Excellent ! Un café virtuel vous est offert !`;
            if (attempts < 5) {
                winsLessThan5++;
                localStorage.setItem('magicGame_winsLessThan5', winsLessThan5.toString());
                if (winsLessThan5 >= 2 && !motivatedForSpaceTrip) {
                    motivatedForSpaceTrip = true;
                    localStorage.setItem('magicGame_motivatedForSpaceTrip', 'true');
                    // Message will be updated by updateRewardInfo on next game init
                }
            }
        }
        showMessage(winMessage, messageType);
    }
    updateRewardInfo(); // Update info for next game
}


function showMessage(text, type) {
    messageDiv.innerHTML = text; // Use innerHTML to allow <br>
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
}

function appendMessage(text) { // To add to existing message
    messageDiv.innerHTML += text;
}

function resetGame() {
    initGame();
}

// Event Listeners
guessButton.addEventListener('click', makeGuess);
resetButton.addEventListener('click', resetGame);
guessInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        makeGuess();
    }
});

// Initialiser le jeu au chargement
initGame();
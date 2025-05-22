<?php
require_once 'sessions.php'; // To ensure header/footer work and for potential win processing
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu du Nombre Magique - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/jeu-nombre-magique.css"> 
    <script src="../Javascript/theme.js" defer></script>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <main class="game-page-main"> 
        <div class="game-container">
            <h1>🎯 Jeu du Nombre Magique</h1>
            <div class="instructions">
                J'ai choisi un nombre entre 1 et 50. À toi de le deviner, Caryl !
            </div>
            
            <div>
                <input type="number" id="guessInput" min="1" max="50" placeholder="?">
                <br>
                <button id="guessButton">Deviner</button>
                <button class="reset-button" id="resetButton">Nouveau jeu</button>
            </div>
            
            <div id="message" class="message" style="display: none;"></div>
            
            <div class="stats">
                <div>Tentatives : <span id="attempts" class="attempts">0</span></div>
            </div>

            <div id="rewardInfo" class="reward-info" style="margin-top: 20px; color: var(--yellow);">
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>
    <script src="../Javascript/jeu-nombre-magique.js" defer></script>
</body>
</html>
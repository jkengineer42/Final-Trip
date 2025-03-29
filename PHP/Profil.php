<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/Profil.css">
</head>
<body>
    <header>
        <a href="Accueil.php" class="logo">FINAL TRIP</a>
        <div class="right">
            <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
            <a href="Destination.php" class="head1">Destination</a>
            <button class="encadré">Contact</button>
            <a href="Connexion.php" class="img1"><img src="../assets/icon/User.png"></a>
            <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png"></a>
        </div>
    </header>

    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="#" method="POST">
                <div class="form-group">
                    <div class="input-container">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                        <a href="#"><img src="../assets/icon/Edit.png" class="edit-icon"></a>
                    </div>

                    <div class="input-container">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                        <a href="#"><img src="../assets/icon/Edit.png" class="edit-icon"></a>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <a href="#"><img src="../assets/icon/Edit.png" class="edit-icon"></a>
                    </div>

                    <div class="input-container">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                        <a href="#"><img src="../assets/icon/Edit.png" class="edit-icon"></a>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" required>
                        <img src="../assets/icon/edit.png" class="edit-icon">
                    </div>
                </div>

                <button type="submit" class="button1">Sauvegarder les modifications</button>
            </form>
        </div>
    </main>

    <footer>
        <h2>Le dernier voyage que vous rêvez d’avoir</h2>
        <div class="contact">
            <p><strong>Adresse :</strong> <a href="#">34, Boulevard Haussmann, Paris 75009</a></p>
            <p><strong>Numéro :</strong> <a href="tel:0749685456">07 49 68 54 56</a></p>
            <p><strong>Email :</strong> <a href="mailto:contact@final-trip.com">contact@final-trip.com</a></p>
        </div>
        <p class="copyright">© 2025 Final Trip, ALL RIGHTS RESERVED.</p>
        <hr class="hr2">
        <div class="links">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Conditions d’utilisations</a>
        </div>
    </footer>
</body>
</html>

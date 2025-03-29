<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Fonction pour valider la date de naissance
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        // Vérifier que l'année est supérieure à 1900
        $year = $d->format('Y');
        if ($year > 1900 && $d->getTimestamp() <= time()) {
            return true;
        }
    }
    return false;
}

// Fonction pour obtenir les informations de l'utilisateur
function getUserData($email) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

    foreach ($jsonData as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Fonction pour mettre à jour les informations de l'utilisateur
function updateUserData($email, $nom, $prenom, $birthdate, $password) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

    for ($i = 0; $i < count($jsonData); $i++) {
        if ($jsonData[$i]['email'] === $email) {
            $jsonData[$i]['nom'] = $nom;
            $jsonData[$i]['prenom'] = $prenom;
            $jsonData[$i]['birthdate'] = $birthdate;
            $jsonData[$i]['password'] = $password;
            break;
        }
    }

    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
}

// Récupérer l'email de l'utilisateur à modifier
$editEmail = isset($_GET['edit']) ? $_GET['edit'] : $_SESSION['user_email'];

// Récupérer les informations de l'utilisateur
$user = getUserData($editEmail);

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $birthdate = htmlspecialchars($_POST['birthdate']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    // Vérification de la date de naissance
    if (!validateDate($birthdate)) {
        $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide ou est dans le futur.</span>";
    } else {
        // Mettre à jour les informations de l'utilisateur
        updateUserData($editEmail, $nom, $prenom, $birthdate, $password);

        // Afficher un message de succès
        $success = "<span style='color: var(--yellow);'>Informations mises à jour avec succès.</span>";
    }
}
?>

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
            <a href="Profil.php" class="img1"><img src="../assets/icon/User.png"></a>
            <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png"></a>
        </div>
    </header>

    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="Profil.php?edit=<?= urlencode($editEmail) ?>" method="POST">
                <?php if (isset($success)): ?>
                    <div class="success"><?= $success ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="input-container">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>

                    <div class="input-container">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>

                    <div class="input-container">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Entrez votre nouveau mot de passe">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" value="<?= date('Y-m-d', strtotime($user['birthdate'])) ?>">
                    </div>
                </div>

                <button type="submit" class="button1">Sauvegarder les modifications</button>
                <a href="logout.php" class="button2">Déconnexion</a>
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
            <a href="#">Conditions d’utilisation</a>
        </div>
    </footer>
</body>
</html>

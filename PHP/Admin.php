<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Lire le fichier JSON existant
$jsonFile = '../data/data_user.json';
$jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// Vérifier si un utilisateur doit être promu en tant qu'administrateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote'])) {
    $emailToPromote = $_POST['email'];
    foreach ($jsonData as &$user) {
        if ($user['email'] === $emailToPromote) {
            $user['is_admin'] = true;
            break;
        }
    }
    // Réécrire le fichier JSON
    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
}

// Vérifier si un utilisateur doit être supprimé
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $emailToDelete = $_POST['email'];
    $canDelete = true;

    // Vérifier si l'utilisateur à supprimer est un administrateur
    foreach ($jsonData as $user) {
        if ($user['email'] === $emailToDelete && $user['is_admin']) {
            $canDelete = false;
            break;
        }
    }

    // Supprimer l'utilisateur s'il n'est pas administrateur
    if ($canDelete) {
        foreach ($jsonData as $key => $user) {
            if ($user['email'] === $emailToDelete) {
                unset($jsonData[$key]);
                break;
            }
        }
        // Réécrire le fichier JSON
        file_put_contents($jsonFile, json_encode(array_values($jsonData), JSON_PRETTY_PRINT));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/Admin.css">
</head>
<body>
    <header>
        <a href="Accueil.php" class="logo">FINAL TRIP</a>
        <div class="right">
            <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
            <a href="Destination.php" class="head1">Destination</a>
            <button class="encadré">Contact</button>
            <a href="Profil.php"><img src="../assets/icon/User.png"></a>
            <a href="#"><img src="../assets/icon/Shopping cart.png"></a>
        </div>
    </header>

    <hr class="hr1">

    <main>
        <h1 class="admin-title"><em>FINAL ADMIN</em></h1>

        <div class="admin-container">
            <div class="filters">
                <input type="text" placeholder="Rechercher par nom/prénom" class="search">
                <input type="text" placeholder="Rechercher par email" class="search">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>NOM</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Date de naissance</th>
                            <th>Administrateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jsonData as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['nom']) ?></td>
                                <td><?= htmlspecialchars($user['prenom']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['birthdate']) ?></td>
                                <td><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
                                <td class="icons">
                                    <a href="Profil.php?edit=<?= urlencode($user['email']) ?>">
                                        <img src="../assets/icon/black_edit.png" alt="Modifier" class="icon">
                                    </a>
                                    <?php if (!$user['is_admin']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                            <button type="submit" name="delete" class="delete-button">
                                                <img src="../assets/icon/delete.png" alt="Supprimer" class="icon delete-icon">
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                            <button type="submit" name="promote" class="promote-button">
                                                <img src="../assets/icon/Admin.png" alt="Admin">
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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

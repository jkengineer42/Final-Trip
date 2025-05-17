<?php
require_once 'sessions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Vérifier si l'utilisateur est un administrateur
$jsonFile = '../data/data_user.json';
$jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
$userEmail = $_SESSION['user_email'];
$isAdmin = false;

foreach ($jsonData as $user) {
    if ($user['email'] === $userEmail && isset($user['is_admin']) && $user['is_admin']) {
        $isAdmin = true;
        break;
    }
}

if (!$isAdmin) {
    header("Location: Accueil.php"); // Rediriger vers la page d'accueil ou une page d'erreur si l'utilisateur n'est pas administrateur
    exit();
}

// Lire le fichier JSON existant
$jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// Nombre d'utilisateurs par page
$usersPerPage = 15;
$totalUsers = count($jsonData);
$totalPages = ceil($totalUsers / $usersPerPage);

// Obtenir la page actuelle à partir de l'URL, par défaut page 1
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

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
    // Rediriger pour recharger la page
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=" . $currentPage);
    exit();
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
        // Rediriger pour recharger la page
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=" . $currentPage);
        exit();
    }
}

// Calculer l'index de début pour la pagination
$startIndex = ($currentPage - 1) * $usersPerPage;
$paginatedUsers = array_slice($jsonData, $startIndex, $usersPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Admin.css">
    <link rel="stylesheet" href="../Css/formulaire.css">
    <script src="../Javascript/admin.js"></script>
    <script src="../Javascript/formulaire.js"></script>
    <script src="../Javascript/theme.js"></script>
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
    </header>

    <hr class="hr1">

    <div class="icon-legend">
        <div class="legend-item">
            <img src="../assets/icon/black_edit.png" alt="Modifier" class="legend-icon">
            <span>Modifier</span>
        </div>
        <div class="legend-item">
            <img src="../assets/icon/delete.png" alt="Supprimer" class="legend-icon">
            <span>Supprimer</span>
        </div>
        <div class="legend-item">
            <img src="../assets/icon/Admin.png" alt="Admin" class="legend-icon">
            <span>Promouvoir en Admin</span>
        </div>
    </div>

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
                        <?php foreach ($paginatedUsers as $user): ?>
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

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="page-link">Précédent</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="page-link">Suivant</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>

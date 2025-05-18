<?php
require_once 'sessions.php';

// Vérifier si l'utilisateur est connecté et est admin (logique existante)
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php");
    exit();
}
$jsonFile = '../data/data_user.json';
$allUsersData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if ($allUsersData === null) { $allUsersData = []; }

$currentUserEmail = $_SESSION['user_email'];
$isAdmin = false;
foreach ($allUsersData as $userAccountCheck) {
    if (isset($userAccountCheck['email']) && $userAccountCheck['email'] === $currentUserEmail && isset($userAccountCheck['is_admin']) && $userAccountCheck['is_admin']) {
        $isAdmin = true;
        break;
    }
}
if (!$isAdmin) {
    header("Location: Accueil.php");
    exit();
}

// --- Récupération des termes de recherche ---
$searchName = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$searchEmail = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';

// --- Filtrage des utilisateurs ---
$filteredUsers = $allUsersData;

if (!empty($searchName)) {
    $searchNameLower = strtolower($searchName);
    $filteredUsers = array_filter($filteredUsers, function($user) use ($searchNameLower) {
        $nom = isset($user['nom']) ? strtolower($user['nom']) : '';
        $prenom = isset($user['prenom']) ? strtolower($user['prenom']) : '';
        // Recherche si le terme est contenu dans le nom, le prénom, ou la combinaison des deux
        return strpos($nom, $searchNameLower) !== false ||
               strpos($prenom, $searchNameLower) !== false ||
               strpos($prenom . ' ' . $nom, $searchNameLower) !== false ||
               strpos($nom . ' ' . $prenom, $searchNameLower) !== false;
    });
}

if (!empty($searchEmail)) {
    $searchEmailLower = strtolower($searchEmail);
    $filteredUsers = array_filter($filteredUsers, function($user) use ($searchEmailLower) {
        $email = isset($user['email']) ? strtolower($user['email']) : '';
        return strpos($email, $searchEmailLower) !== false;
    });
}
$filteredUsers = array_values($filteredUsers); // Réindexer après filtrage

// Gestion des actions POST (Promouvoir, Supprimer)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $actionTaken = false;
    if (isset($_POST['promote'])) {
        $emailToPromote = $_POST['email'];
        foreach ($allUsersData as &$user) { // Note: on modifie $allUsersData ici car c'est ce qui sera sauvegardé
            if ($user['email'] === $emailToPromote) {
                $user['is_admin'] = true;
                $actionTaken = true;
                break;
            }
        }
    } elseif (isset($_POST['delete'])) {
        $emailToDelete = $_POST['email'];
        $canDelete = true;
        foreach ($allUsersData as $key => $user) {
            if ($user['email'] === $emailToDelete) {
                if ($user['is_admin']) { // On ne peut pas supprimer un admin via ce bouton
                    $canDelete = false;
                    // Optionnel: ajouter un message d'erreur ici si besoin
                    break;
                }
                if ($canDelete) {
                    unset($allUsersData[$key]);
                    $allUsersData = array_values($allUsersData); // Réindexer
                    $actionTaken = true;
                }
                break;
            }
        }
    }

    if ($actionTaken) {
        file_put_contents($jsonFile, json_encode($allUsersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        // Rediriger pour refléter les changements et conserver les filtres et la page actuelle
        $redirectParams = [];
        if (!empty($searchName)) $redirectParams['search_name'] = $searchName;
        if (!empty($searchEmail)) $redirectParams['search_email'] = $searchEmail;
        // Recalculer currentPage pour la redirection est complexe si des éléments sont supprimés.
        // Pour simplifier, on peut rediriger vers la première page des filtres.
        // Ou tenter de garder la page actuelle, mais elle pourrait être vide.
        $currentPageForRedirect = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPageForRedirect > 1) $redirectParams['page'] = $currentPageForRedirect;

        header("Location: " . $_SERVER['PHP_SELF'] . (!empty($redirectParams) ? '?' . http_build_query($redirectParams) : ''));
        exit();
    }
}


// --- Pagination (appliquée aux utilisateurs filtrés) ---
$usersPerPage = 15;
$totalUsers = count($filteredUsers);
$totalPages = ceil($totalUsers / $usersPerPage);
$totalPages = max(1, $totalPages); // Au moins une page

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); 

$startIndex = ($currentPage - 1) * $usersPerPage;
$paginatedUsers = array_slice($filteredUsers, $startIndex, $usersPerPage);

// Préparer les paramètres GET pour les liens de pagination
$paginationQueryHttp = [];
if (!empty($searchName)) $paginationQueryHttp['search_name'] = $searchName;
if (!empty($searchEmail)) $paginationQueryHttp['search_email'] = $searchEmail;
$paginationQueryString = http_build_query($paginationQueryHttp);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Admin.css">
    <script src="../Javascript/theme.js" defer></script>
    <script src="../Javascript/admin.js" defer></script> 
</head>
<body>
    <?php include 'header.php'; // Correction: ne pas entourer d'une balise <header> ici ?>
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
            <form method="GET" action="Admin.php" class="admin-filters-form">
                <div class="filters">
                    <input type="text" name="search_name" placeholder="Rechercher par nom/prénom" class="search" value="<?= htmlspecialchars($searchName) ?>">
                    <input type="text" name="search_email" placeholder="Rechercher par email" class="search" value="<?= htmlspecialchars($searchEmail) ?>">
                    <button type="submit" class="button-search">Rechercher</button>
                    <a href="Admin.php" class="button-search">Réinitialiser</a>
                </div>
                <?php // Les paramètres de page seront gérés par les liens de pagination ?>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>NOM</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Date de naissance</th>
                            <th>Administrateur</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginatedUsers)): ?>
                            <tr>
                                <td colspan="6">Aucun utilisateur trouvé correspondant à vos critères de recherche.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paginatedUsers as $user): ?>

                                <tr>
                                    <td><?= htmlspecialchars($user['nom'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['prenom'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['birthdate'] ?? 'N/A') ?></td>
                                    <td><?= (isset($user['is_admin']) && $user['is_admin']) ? 'Oui' : 'Non' ?></td>
                                    <td><?= (isset($user['is_blocked']) && $user['is_blocked']) ? '<span style="color:red;">Bloqué</span>' : 'Actif' ?></td> 
                                    <td class="icons"> 
                                        <a href="Profil.php?edit=<?= urlencode($user['email']) ?>">
                                            <img src="../assets/icon/black_edit.png" alt="Modifier" class="icon">
                                        </a>
                                        <?php if (!(isset($user['is_admin']) && $user['is_admin'])): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                                <input type="hidden" name="page" value="<?= $currentPage ?>">
                                                <?php if (!empty($searchName)): ?><input type="hidden" name="search_name" value="<?= htmlspecialchars($searchName) ?>"><?php endif; ?>
                                                <?php if (!empty($searchEmail)): ?><input type="hidden" name="search_email" value="<?= htmlspecialchars($searchEmail) ?>"><?php endif; ?>
                                                <button type="submit" name="delete" class="delete-button" title="Supprimer l'utilisateur">
                                                    <img src="../assets/icon/delete.png" alt="Supprimer" class="icon delete-icon">
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                                <input type="hidden" name="page" value="<?= $currentPage ?>">
                                                <?php if (!empty($searchName)): ?><input type="hidden" name="search_name" value="<?= htmlspecialchars($searchName) ?>"><?php endif; ?>
                                                <?php if (!empty($searchEmail)): ?><input type="hidden" name="search_email" value="<?= htmlspecialchars($searchEmail) ?>"><?php endif; ?>
                                                <button type="submit" name="promote" class="promote-button" title="Promouvoir en administrateur">
                                                    <img src="../assets/icon/Admin.png" alt="Promouvoir Admin">
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?><?= !empty($paginationQueryString) ? '&'.$paginationQueryString : '' ?>" class="page-link">Précédent</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($paginationQueryString) ? '&'.$paginationQueryString : '' ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?><?= !empty($paginationQueryString) ? '&'.$paginationQueryString : '' ?>" class="page-link">Suivant</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>

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
            if ($password) {
                $jsonData[$i]['password'] = $password;
            }
            break;
        }
    }

    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
}

// Récupérer l'email de l'utilisateur à modifier
$editEmail = isset($_GET['edit']) ? $_GET['edit'] : $_SESSION['user_email'];

// Récupérer les informations de l'utilisateur
$user = getUserData($editEmail);

// Vérifier si l'utilisateur est un administrateur
$isAdmin = false;
$jsonFile = '../data/data_user.json';
$jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

foreach ($jsonData as $userData) {
    if ($userData['email'] === $_SESSION['user_email'] && $userData['is_admin']) {
        $isAdmin = true;
        break;
    }
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $birthdate = htmlspecialchars($_POST['birthdate']);
    $password = null;

    // Vérifier si l'utilisateur modifie son propre profil
    if ($_SESSION['user_email'] === $editEmail && !empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

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
     <?php include('header.php'); ?>

    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="Profil.php?edit=<?= urlencode($editEmail) ?>" method="POST" id="profileForm">
                <?php if (isset($success)): ?>
                    <div class="success"><?= $success ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                <div class="admin-link">
                    <a href="Admin.php" class="button2">Accéder à la page Admin</a>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="input-container">
                        <label for="nom">Nom</label>
                        <!-- Modifié : Ajout de la classe form-field pour être reconnu par le JS -->
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" class="form-field disabled" readonly required>
                        
                        <!-- Modifié : Remplacement de l'image par un bouton -->
                        <button type="button" class="edit-btn" data-field="nom">Modifier</button>
                        
                        <!-- Point 3 : Ajout des boutons Valider/Annuler -->
                        <div class="action-buttons" style="display: none;">
                            <button type="button" class="save-btn" data-field="nom">Valider</button>
                            <button type="button" class="cancel-btn" data-field="nom">Annuler</button>
                        </div>
                    </div>

                    <div class="input-container">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" class="form-field disabled" readonly required>
                        <button type="button" class="edit-btn" data-field="prenom">Modifier</button>
                        <div class="action-buttons" style="display: none;">
                            <button type="button" class="save-btn" data-field="prenom">Valider</button>
                            <button type="button" class="cancel-btn" data-field="prenom">Annuler</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-field disabled" disabled>
                    </div>

                    <?php if ($_SESSION['user_email'] === $editEmail): ?>
                        <div class="input-container">
                            <label for="password">Mot de passe</label>
                            <input type="password" id="password" name="password" placeholder="Entrez votre nouveau mot de passe" class="form-field disabled" readonly>
                            <button type="button" class="edit-btn" data-field="password">Modifier</button>
                            <div class="action-buttons" style="display: none;">
                                <button type="button" class="save-btn" data-field="password">Valider</button>
                                <button type="button" class="cancel-btn" data-field="password">Annuler</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" value="<?= date('Y-m-d', strtotime($user['birthdate'])) ?>" class="form-field disabled" readonly>
                        <button type="button" class="edit-btn" data-field="birthdate">Modifier</button>
                        <div class="action-buttons" style="display: none;">
                            <button type="button" class="save-btn" data-field="birthdate">Valider</button>
                            <button type="button" class="cancel-btn" data-field="birthdate">Annuler</button>
                        </div>
                    </div>
                </div>

                <!-- Modifié : Bouton de soumission caché par défaut -->
                <button type="submit" class="button1" id="submitBtn" style="display: none;">Sauvegarder les modifications</button>
                <a href="logout.php" class="button2">Déconnexion</a>
            </form>
        </div>
    </main>

    <footer><?php include('footer.php'); ?></footer>
    
    <!-- Modifié : Vérification du chemin du fichier JavaScript -->
    <script src="../Javascript/profil.js"></script>
</body>
</html>

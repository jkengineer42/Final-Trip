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
            <a href="Connexion.php"><img src="../assets/icon/User.png"></a>
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
                            <th>Bloqué</th>
                            <th>Niveau XTREME</th>
                            <th>Restrictions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Dupont</td><td>Jean</td><td>jean.dupont@gmail.com</td><td>Non</td><td>3</td><td>Asthme</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Meo</td><td>Dimi</td><td>neo.dimi@gmail.com</td><td>Non</td><td>2</td><td>Aucune</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Leroy</td><td>Camille</td><td>camille.leroy@gmail.com</td><td>Non</td><td>4</td><td>Allergie aux noix</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Martin</td><td>Sophie</td><td>sophie.martin@email.com</td><td>Non</td><td>1</td><td>Diabète</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Dubois</td><td>Lucas</td><td>lucas.dubois@email.com</td><td>Oui</td><td>3</td><td>Aucune</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Moreau</td><td>Emma</td><td>emma.moreau@email.com</td><td>Non</td><td>2</td><td>Asthme</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Bernard</td><td>Hugo</td><td>hugo.bernard@email.com</td><td>Non</td><td>4</td><td>Hypertension</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Thomas</td><td>Chloé</td><td>chloe.thomas@email.com</td><td>Non</td><td>2</td><td>Aucune</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Petit</td><td>Louis</td><td>louis.petit@email.com</td><td>Non</td><td>1</td><td>Allergie aux fruits de mer</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Lemoine</td><td>Julie</td><td>julie.lemoine@email.com</td><td>Non</td><td>3</td><td>Diabète</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Rousseau</td><td>Paul</td><td>paul.rousseau@email.com</td><td>Non</td><td>2</td><td>Aucune</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Vincent</td><td>Marie</td><td>marie.vincent@email.com</td><td>Non</td><td>4</td><td>Mal de Mer</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Giraud</td><td>Antoine</td><td>antoine.giraud@email.com</td><td>Non</td><td>1</td><td>Asthme</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Marchand</td><td>Isabelle</td><td>isabelle.marchand@email.com</td><td>Oui</td><td>3</td><td>Aucune</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
                        <tr><td>Lefebvre</td><td>Maxime</td><td>maxime.lefebvre@email.com</td><td>Non</td><td>2</td><td>Allergie au pollen</td><td class="icons"><img src="../assets/icon/black_edit.png" alt="Modifier" class="icon"><img src="../assets/icon/delete.png" alt="Supprimer" class="icon"></td></tr>
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
            <a href="#">Conditions d’utilisations</a>
        </div>
    </footer>
</body>
</html>

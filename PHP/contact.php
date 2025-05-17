<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/contact.css">
    <link rel="stylesheet" href="../Css/root.css"> 
    <script src="../Javascript/theme.js" defer></script>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <div class="contact-container">
            <h1>Contactez-nous</h1>
            <p class="intro-text">Une question, une suggestion, ou besoin d'aide pour planifier votre prochain voyage ? N'hésitez pas à nous contacter !</p>

            <div class="contact-content">
                <div class="contact-form-section">
                    <h2>Envoyez-nous un message</h2>
                    <!-- This form will attempt to open the user's default email client -->
                    <form action="mailto:contact@final-trip.com" method="post" enctype="text/plain">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" placeholder="Votre nom et prénom" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Votre adresse email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" placeholder="Sujet de votre message" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" placeholder="Écrivez votre message ici..." required></textarea>
                        </div>
                        <button type="submit" class="submit-button">Envoyer le message</button>
                    </form>
                </div>

                <div class="contact-info-section">
                    <h2>Nos coordonnées</h2>
                    <div class="info-item">
                        <img src="../assets/icon/location_pin.svg" alt="Adresse" class="info-icon">
                        <div>
                            <h3>Adresse</h3>
                            <p><a href="https://maps.google.com/?q=34+Boulevard+Haussmann+Paris+75009" target="_blank" rel="noopener noreferrer">34, Boulevard Haussmann, Paris 75009</a></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <img src="../assets/icon/phone.svg" alt="Téléphone" class="info-icon">
                        <div>
                            <h3>Téléphone</h3>
                            <p><a href="tel:0749685456">07 49 68 54 56</a></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <img src="../assets/icon/mail.svg" alt="Email" class="info-icon">
                        <div>
                            <h3>Email</h3>
                            <p><a href="mailto:contact@final-trip.com">contact@final-trip.com</a></p>
                        </div>
                    </div>
                    
                    <h3>Horaires d'ouverture</h3>
                    <p>Lundi - Vendredi : 9h00 - 18h00</p>
                    <p>Samedi : 10h00 - 16h00</p>
                    <p>Dimanche : Fermé</p>

                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.363804290318!2d2.332760015674904!3d48.87166697928886!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e3b1c33ae4d%3A0x8f0d63c274962581!2s34%20Bd%20Haussmann%2C%2075009%20Paris!5e0!3m2!1sfr!2sfr!4v1620000000000!5m2!1sfr!2sfr" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>
    <script src="../Javascript/menu.js"></script>
</body>
</html>

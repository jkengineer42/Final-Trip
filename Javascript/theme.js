
// Attendre que le document soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // Créer le sélecteur de thème s'il n'existe pas déjà
    creerSelecteurTheme();
    
    // Initialiser le thème en fonction du cookie
    initialiserTheme();
    
    // Fonction pour créer le sélecteur de thème
    function creerSelecteurTheme() {
        // Trouver l'élément où placer le sélecteur
        var header = document.querySelector('header .right');
        if (!header) return;
        
        // Chercher si le sélecteur existe déjà
        var selecteurExistant = document.querySelector('.theme-selector');
        if (selecteurExistant) return;
        
        // Créer le conteneur du sélecteur
        var selecteur = document.createElement('div');
        selecteur.className = 'theme-selector';
        
        // Créer le bouton pour le mode sombre
        var btnSombre = document.createElement('button');
        btnSombre.className = 'theme-btn dark-btn';
        btnSombre.setAttribute('data-theme', 'sombre');
        btnSombre.innerHTML = '<img src="../assets/icon/moon.png" alt="Mode sombre" style="width:18px;height:18px;">';
        btnSombre.addEventListener('click', function() { changerTheme('sombre'); });
        
        // Créer le bouton pour le mode clair
        var btnClair = document.createElement('button');
        btnClair.className = 'theme-btn light-btn';
        btnClair.setAttribute('data-theme', 'clair');
        btnClair.innerHTML = '<img src="../assets/icon/sun.png" alt="Mode clair" style="width:18px;height:18px;">';
        btnClair.addEventListener('click', function() { changerTheme('clair'); });
        
        // Ajouter les boutons au sélecteur
        selecteur.appendChild(btnSombre);
        selecteur.appendChild(btnClair);
        
        // Trouver l'élément après lequel insérer le sélecteur
        var boutonContact = header.querySelector('.encadré');
        if (boutonContact) {
            // Insérer après le bouton Contact
            boutonContact.parentNode.insertBefore(selecteur, boutonContact.nextSibling);
        } else {
            // Sinon, ajouter à la fin du header
            header.appendChild(selecteur);
        }
    }
    
    // Fonction pour initialiser le thème selon le cookie
    function initialiserTheme() {
        // Récupérer la valeur du cookie
        var themeCookie = lireCookie('theme');
        
        // Appliquer le thème
        if (themeCookie === 'clair') {
            changerTheme('clair');
        } else {
            changerTheme('sombre');
        }
    }
    
    // Fonction pour changer de thème
    function changerTheme(theme) {
        // Mettre à jour l'état de la page
        if (theme === 'clair') {
            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
            appliquerModeClair();
        } else {
            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
            supprimerModeClair();
        }
        
        // Enregistrer le choix dans un cookie
        creerCookie('theme', theme, 30);
    }
    
    // Fonction pour appliquer le mode clair
    function appliquerModeClair() {
        // Si le lien CSS n'existe pas déjà, l'ajouter
        if (!document.getElementById('theme-clair')) {
            var lienCss = document.createElement('link');
            lienCss.rel = 'stylesheet';
            lienCss.href = '../Css/light-mode.css';
            lienCss.id = 'theme-clair';
            
            document.head.appendChild(lienCss);
        }
    }
    
    // Fonction pour supprimer le mode clair
    function supprimerModeClair() {
        // Supprimer le lien CSS s'il existe
        var lienCss = document.getElementById('theme-clair');
        if (lienCss) {
            lienCss.parentNode.removeChild(lienCss);
        }
    }
    
    // Fonction pour créer un cookie
    function creerCookie(nom, valeur, jours) {
        var dateExpiration = '';
        
        if (jours) {
            var date = new Date();
            date.setTime(date.getTime() + (jours * 24 * 60 * 60 * 1000));
            dateExpiration = '; expires=' + date.toUTCString();
        }
        
        document.cookie = nom + '=' + valeur + dateExpiration + '; path=/';
    }
    
    // Fonction pour lire un cookie
    function lireCookie(nom) {
        var nomEgal = nom + '=';
        var cookies = document.cookie.split(';');
        
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1, cookie.length);
            }
            
            if (cookie.indexOf(nomEgal) === 0) {
                return cookie.substring(nomEgal.length, cookie.length);
            }
        }
        
        return null;
    }
});

// vérifier si pas de pb ou pas de pas

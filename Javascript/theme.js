// Système de gestion des thèmes clair/sombre avec persistance par cookie

document.addEventListener('DOMContentLoaded', function() {
   // Initialisation : création de l'interface et application du thème sauvegardé
   creerSelecteurTheme();
   initialiserTheme();
   
   // Génère dynamiquement les boutons de sélection de thème dans le header
   function creerSelecteurTheme() {
       // Recherche la zone droite du header pour placer les boutons de thème
       var header = document.querySelector('header .right');
       if (!header) return; // Sécurité : sortir si l'élément n'existe pas
       
       // Vérification pour éviter la création de doublons
       var selecteurExistant = document.querySelector('.theme-selector');
       if (selecteurExistant) return; // Le sélecteur existe déjà, on sort
       
       // Création du conteneur principal pour les boutons
       var selecteur = document.createElement('div');
       selecteur.className = 'theme-selector'; // Classe CSS pour le style du conteneur
       
       // Création du bouton mode sombre avec icône lune
       var btnSombre = document.createElement('button');
       btnSombre.className = 'theme-btn dark-btn';       
       btnSombre.setAttribute('data-theme', 'sombre');    
       btnSombre.innerHTML = '<img src="../assets/icon/moon.png" alt="Mode sombre" style="width:18px;height:18px;">'; // Icône lune 18x18px
       btnSombre.addEventListener('click', function() { changerTheme('sombre'); }); // Gestionnaire de clic
       
       // Création du bouton mode clair avec icône soleil
       var btnClair = document.createElement('button');
       btnClair.className = 'theme-btn light-btn';        
       btnClair.setAttribute('data-theme', 'clair');      
       btnClair.innerHTML = '<img src="../assets/icon/sun.png" alt="Mode clair" style="width:18px;height:18px;">'; // Icône soleil 18x18px
       btnClair.addEventListener('click', function() { changerTheme('clair'); }); // Gestionnaire de clic
       
       // Ajout des boutons au conteneur
       selecteur.appendChild(btnSombre);
       selecteur.appendChild(btnClair);
       
       // Positionnement dans le header : après le bouton Contact ou à la fin
       var boutonContact = header.querySelector('.encadré'); 
       if (boutonContact) {
           // Insertion après le bouton Contact pour respecter l'ordre visuel
           boutonContact.parentNode.insertBefore(selecteur, boutonContact.nextSibling);
       } else {
           // Si pas de bouton Contact, ajout à la fin du header
           header.appendChild(selecteur);
       }
   }
   
   // Applique le thème sauvegardé dans les cookies ou le thème sombre par défaut
   function initialiserTheme() {
       // Récupération de la préférence utilisateur depuis les cookies
       var themeCookie = lireCookie('theme');
       
       // Application du thème selon la valeur du cookie
       if (themeCookie === 'clair') {
           changerTheme('clair');
       } else {
           changerTheme('sombre'); // Thème par défaut si pas de cookie ou valeur différente
       }
   }
   
   // Gère la transition entre les thèmes et sauvegarde le choix utilisateur
   function changerTheme(theme) {
       if (theme === 'clair') {
           // Activation du mode clair : ajout des classes CSS et chargement du fichier CSS dédié
           document.body.classList.add('light-mode');      
           document.body.classList.remove('dark-mode');    
           appliquerModeClair(); // Chargement du fichier CSS spécifique au mode clair
       } else {
           // Activation du mode sombre : retour au CSS par défaut du site
           document.body.classList.add('dark-mode');      
           document.body.classList.remove('light-mode');   
           supprimerModeClair(); // Suppression du fichier CSS du mode clair
       }
       
       // Sauvegarde du choix utilisateur pour 30 jours
       creerCookie('theme', theme, 30);
   }
   
   // Charge dynamiquement le fichier CSS spécifique au mode clair
   function appliquerModeClair() {
       // Vérification pour éviter les doublons de balises <link>
       if (!document.getElementById('theme-clair')) {
           var lienCss = document.createElement('link');
           lienCss.rel = 'stylesheet';                    
           lienCss.href = '../Css/light-mode.css';        
           lienCss.id = 'theme-clair';                    
           
           // Ajout de la balise <link> dans le <head> du document
           document.head.appendChild(lienCss);
       }
   }
   
   // Supprime le fichier CSS du mode clair pour revenir au thème sombre par défaut
   function supprimerModeClair() {
       // Recherche et suppression de la balise <link> du mode clair
       var lienCss = document.getElementById('theme-clair');
       if (lienCss) {
           lienCss.parentNode.removeChild(lienCss); // Suppression du DOM
       }
   }
   
   // Utilitaire pour créer un cookie avec date d'expiration
   function creerCookie(nom, valeur, jours) {
       var dateExpiration = '';
       
       // Calcul de la date d'expiration si une durée est spécifiée
       if (jours) {
           var date = new Date();
           date.setTime(date.getTime() + (jours * 24 * 60 * 60 * 1000)); // Conversion jours -> millisecondes
           dateExpiration = '; expires=' + date.toUTCString(); // Format UTC pour la compatibilité
       }
       
       // Création du cookie avec path global pour tout le site
       document.cookie = nom + '=' + valeur + dateExpiration + '; path=/';
   }
   
   // Utilitaire pour lire la valeur d'un cookie spécifique
   function lireCookie(nom) {
       var nomEgal = nom + '='; // Pattern de recherche avec le signe égal
       var cookies = document.cookie.split(';'); // Séparation de tous les cookies
       
       // Parcours de tous les cookies pour trouver celui recherché
       for (var i = 0; i < cookies.length; i++) {
           var cookie = cookies[i];
           
           // Suppression des espaces en début de chaîne
           while (cookie.charAt(0) === ' ') {
               cookie = cookie.substring(1, cookie.length);
           }
           
           // Vérification si le cookie correspond au nom recherché
           if (cookie.indexOf(nomEgal) === 0) {
               return cookie.substring(nomEgal.length, cookie.length); // Retour de la valeur seule
           }
       }
       
       return null; // Cookie non trouvé
   }
});

// vérifier si pas de pb ou pas de pas

// Gestion de la navigation mobile responsive et du positionnement du sélecteur de thème
document.addEventListener('DOMContentLoaded', () => {
    
    // Sélection des éléments principaux pour le menu mobile
    const hamburger = document.querySelector('.hamburger');     // Bouton hamburger (☰)
    const mobileMenu = document.querySelector('.mobile-menu');  // Menu coulissant mobile
    const body = document.body;                                 // Pour contrôler le scroll

    // Conteneurs pour repositionner le sélecteur de thème selon le contexte
    const desktopNavContainer = document.querySelector('header .right.desktop-nav');
    const mobileThemePlaceholder = document.querySelector('.mobile-menu .mobile-theme-selector-placeholder');

    // Fonction pour repositionner le sélecteur de thème selon la taille d'écran et l'état du menu
    function moveThemeSelector() {
        const themeSelector = document.querySelector('.theme-selector'); // Créé dynamiquement par theme.js
        if (!themeSelector) {
            // console.warn('Theme selector not found. Ensure theme.js has run.');
            return; // Sortir si le sélecteur n'existe pas encore
        }

        // Cas 1: Mobile avec menu ouvert - déplacer le sélecteur dans le menu mobile
        if (window.innerWidth <= 992 && mobileMenu.classList.contains('active')) {
            if (mobileThemePlaceholder && !mobileThemePlaceholder.contains(themeSelector)) {
                mobileThemePlaceholder.appendChild(themeSelector);
            }
        } 
        // Cas 2: Mobile avec menu fermé - remettre le sélecteur dans la navigation principale
        else if (window.innerWidth <= 992 && !mobileMenu.classList.contains('active')) {
             if (desktopNavContainer && mobileThemePlaceholder.contains(themeSelector)) {
                 desktopNavContainer.appendChild(themeSelector); // Garder l'accès même menu fermé
             }
        }
        // Cas 3: Desktop - s'assurer que le sélecteur est dans la navigation desktop
        else {
            if (desktopNavContainer && !desktopNavContainer.contains(themeSelector)) {
                desktopNavContainer.appendChild(themeSelector);
            }
        }
    }

    // Gestionnaire de clic sur le bouton hamburger
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');   // Animation du bouton (☰ ↔ ×)
            mobileMenu.classList.toggle('active');  // Ouverture/fermeture du menu coulissant
            body.classList.toggle('no-scroll');     // Empêcher le scroll de la page quand menu ouvert

            // Repositionner le sélecteur de thème après le changement d'état du menu
            moveThemeSelector();
        });
    }

    // Gestionnaire de redimensionnement de fenêtre 
    window.addEventListener('resize', () => {
        // Si on passe en mode desktop, fermer automatiquement le menu mobile
        if (window.innerWidth > 992) {
            if (mobileMenu.classList.contains('active')) {
                hamburger.classList.remove('active');  // Remettre hamburger normal
                mobileMenu.classList.remove('active'); // Fermer le menu
                body.classList.remove('no-scroll');    // Réactiver le scroll
            }
        }
        // Toujours repositionner le sélecteur selon la nouvelle taille d'écran
        moveThemeSelector();
    });

    // Positionnement initial du sélecteur au chargement de la page
    moveThemeSelector();

    // Fermeture automatique du menu mobile lors du clic sur un lien de navigation
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Si le menu est ouvert, le fermer après clic sur lien
            if (mobileMenu.classList.contains('active')) {
                hamburger.classList.remove('active');  // Hamburger redevient normal
                mobileMenu.classList.remove('active'); // Menu se ferme
                body.classList.remove('no-scroll');    // Page redevient scrollable
                moveThemeSelector();                    // Repositionner le sélecteur
            }
            // Le navigateur suivra ensuite automatiquement le lien
        });
    });
});



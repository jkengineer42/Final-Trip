
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerButton = document.getElementById('hamburger-button');
    const navLinksContainer = document.getElementById('nav-links-container');

    if (hamburgerButton && navLinksContainer) {
        hamburgerButton.addEventListener('click', function() {
            // Basculer la classe 'active' sur le conteneur des liens
            navLinksContainer.classList.toggle('active');

            // Basculer la classe 'active' sur le bouton hamburger (pour l'animation en croix)
            hamburgerButton.classList.toggle('active');

            // Mettre à jour l'attribut aria-expanded pour l'accessibilité
            const isExpanded = hamburgerButton.getAttribute('aria-expanded') === 'true' || false;
            hamburgerButton.setAttribute('aria-expanded', !isExpanded);
        });
    }
});
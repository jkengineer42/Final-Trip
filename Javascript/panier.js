document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons d'action du panier
    const removeButtons = document.querySelectorAll('.remove-button');
    const emptyCartButton = document.querySelector('.secondary-button[href="panier.php?action=vider"]');
    
    // Animation pour les messages de statut
    const statusMessages = document.querySelectorAll('.success-message, .info-message, .error-message');
    if (statusMessages.length > 0) {
        // Faire disparaître les messages après 5 secondes
        setTimeout(function() {
            statusMessages.forEach(function(message) {
                message.style.opacity = '1';
                fadeOut(message, 500);
            });
        }, 5000);
    }
    
    // Ajouter une animation lors du survol des articles du panier
    const panierItems = document.querySelectorAll('.panier-item');
    panierItems.forEach(function(item) {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.3)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.2)';
        });
    });
    
    // Fonction pour faire disparaître un élément en douceur
    function fadeOut(element, duration) {
        let opacity = 1;
        const interval = 20;
        const delta = interval / duration;
        
        const fadeEffect = setInterval(function() {
            opacity -= delta;
            element.style.opacity = opacity;
            
            if (opacity <= 0) {
                clearInterval(fadeEffect);
                element.style.display = 'none';
            }
        }, interval);
    }
});

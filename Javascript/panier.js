// Exécute le code une fois que la page HTML est complètement chargée
document.addEventListener('DOMContentLoaded', function() {

    // Récupère tous les boutons "retirer un article"
    const removeButtons = document.querySelectorAll('.remove-button');

    // Récupère le bouton "vider le panier"
    const emptyCartButton = document.querySelector('.secondary-button[href="panier.php?action=vider"]');

    // Récupère tous les messages (succès, info ou erreur)
    const statusMessages = document.querySelectorAll('.success-message, .info-message, .error-message');

    // Si des messages sont présents, les faire disparaître après 5 secondes
    if (statusMessages.length > 0) {
        setTimeout(function() {
            statusMessages.forEach(function(message) {
                message.style.opacity = '1';
                fadeOut(message, 500); // appel de l'animation
            });
        }, 5000);
    }

    // Applique une animation visuelle aux articles du panier au survol
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

    // Fonction qui fait disparaître un élément en fondu
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

    // Fonction globale pour vider le panier
    window.viderPanier = function() {
        if (confirm("Êtes-vous sûr de vouloir vider votre panier ?")) {
            fetch('panier.php?action=vider', {
                method: 'GET'
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // recharge la page si tout s’est bien passé
                }
            })
            .catch(error => {
                console.error('Erreur:', error); // affiche une erreur si la requête échoue
            });
        }
    };

});

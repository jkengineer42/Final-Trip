// Gestion de l'interface utilisateur du panier d'achat
document.addEventListener('DOMContentLoaded', function() {

    // Sélection des éléments d'interface du panier
    const removeButtons = document.querySelectorAll('.remove-button');     // Boutons "supprimer" de chaque article
    const emptyCartButton = document.querySelector('.secondary-button[href="panier.php?action=vider"]'); // Bouton "vider le panier"

    // Gestion automatique des messages de notification
    const statusMessages = document.querySelectorAll('.success-message, .info-message, .error-message');
    if (statusMessages.length > 0) {
        // Faire disparaître les messages après 5 secondes
        setTimeout(function() {
            statusMessages.forEach(function(message) {
                message.style.opacity = '1';        
                fadeOut(message, 500);               // Lancer l'animation de disparition (500ms)
            });
        }, 5000); // Délai de 5 secondes avant disparition
    }

    // Animation de survol pour les articles du panier
    const panierItems = document.querySelectorAll('.panier-item');
    panierItems.forEach(function(item) {
        // Animation à l'entrée de la souris - effet de "soulèvement"
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';                   
            this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.3)';     
        });

        // Animation à la sortie de la souris - retour à l'état normal
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';                      // Remettre à la position normale
            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.2)';     
        });
    });

    // Fonction d'animation pour faire disparaître un élément progressivement
    function fadeOut(element, duration) {
        let opacity = 1;                        // Opacité de départ (100%)
        const interval = 20;                    // Mise à jour toutes les 20ms 
        const delta = interval / duration;      // Calcul de la diminution d'opacité par étape

        // Boucle d'animation
        const fadeEffect = setInterval(function() {
            opacity -= delta;                   // Diminuer l'opacité progressivement
            element.style.opacity = opacity;    // Appliquer la nouvelle opacité

            // Arrêter l'animation quand l'élément devient invisible
            if (opacity <= 0) {
                clearInterval(fadeEffect);      // Stopper l'intervalle
                element.style.display = 'none'; // Cacher complètement l'élément du layout
            }
        }, interval);
    }

    // Fonction globale pour vider entièrement le panier
    window.viderPanier = function() {
        // Demander confirmation à l'utilisateur avant de vider
        if (confirm("Êtes-vous sûr de vouloir vider votre panier ?")) {
            // Envoyer une requête AJAX au serveur pour vider le panier
            fetch('panier.php?action=vider', {
                method: 'GET'                   
            })
            .then(response => {
                // Si la requête a réussi, recharger la page pour afficher le panier vide
                if (response.ok) {
                    window.location.reload();
                }
            })
            .catch(error => {
                // Afficher l'erreur dans la console si la requête échoue
                console.error('Erreur lors du vidage du panier:', error);
            });
        }
        // Si l'utilisateur annule, ne rien faire
    };

});

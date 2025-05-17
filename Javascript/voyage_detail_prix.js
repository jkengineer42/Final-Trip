document.addEventListener('DOMContentLoaded', () => {
    const basePriceElement = document.getElementById('base-price');
    const estimatedPriceElement = document.getElementById('estimated-price');
    const hiddenEstimatedPriceInput = document.getElementById('hidden-estimated-price'); // Récupérer le champ caché
    const form = document.getElementById('trip-customization-form');

    if (!basePriceElement || !estimatedPriceElement || !form || !hiddenEstimatedPriceInput) {
        console.error("Éléments nécessaires pour le calcul du prix non trouvés.");
        return;
    }

    const basePrice = parseFloat(basePriceElement.value) || 0;

    function formatPrice(price) {
        // Ajoute le symbole € et formate le nombre
        return price.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
    }

    function updateEstimatedPrice() {
        let currentTotal = basePrice;
        const selectedOptions = {}; // Pour stocker le prix de chaque option sélectionnée par étape/catégorie

        // Sélectionne tous les inputs radio cochés et les selects de participants
        const inputs = form.querySelectorAll('input[type="radio"]:checked, select.participants-select');

        inputs.forEach(input => {
            const stageId = input.dataset.stageId;
            const category = input.dataset.category;
            const priceStr = input.dataset.price; // Pour les radios
            const participants = (category === 'participants') ? parseInt(input.value, 10) : 1; // Pour les selects

            if (!stageId) return; // Ignorer si pas d'ID d'étape

            if (!selectedOptions[stageId]) {
                selectedOptions[stageId] = {};
            }

            if (category !== 'participants' && priceStr) {
                 const price = parseFloat(priceStr) || 0;
                 if (price > 0) {
                     // Stocker le prix unitaire de l'option
                     selectedOptions[stageId][category] = price;
                 } else {
                     // Si le prix est 0 ou inclus, s'assurer qu'il n'y a pas de prix précédent stocké
                     delete selectedOptions[stageId][category];
                 }
            } else if (category === 'participants') {
                // Stocker le nombre de participants
                 selectedOptions[stageId]['participants'] = participants;
            }
        });

        // Calculer le prix total en tenant compte des participants pour les activités
        Object.keys(selectedOptions).forEach(stageId => {
            let stageOptions = selectedOptions[stageId];
            let activityPrice = stageOptions['activites'] || 0;
            let numParticipants = stageOptions['participants'] || 1;

             // Ajouter le prix de l'activité multiplié par les participants
             currentTotal += activityPrice * numParticipants;

            // Ajouter les prix des autres catégories (qui ne dépendent pas des participants)
            Object.keys(stageOptions).forEach(cat => {
                if (cat !== 'activites' && cat !== 'participants') {
                    currentTotal += stageOptions[cat] || 0;
                }
            });
        });


        // Met à jour l'affichage du prix estimé
        if (estimatedPriceElement) {
            estimatedPriceElement.textContent = formatPrice(currentTotal);
            // Ajouter une classe pour une éventuelle animation
            estimatedPriceElement.classList.add('price-updated');
            setTimeout(() => estimatedPriceElement.classList.remove('price-updated'), 500);
        }

        // *** METTRE À JOUR LE CHAMP CACHÉ ***
        if (hiddenEstimatedPriceInput) {
            hiddenEstimatedPriceInput.value = currentTotal.toFixed(2); // Stocke avec 2 décimales
        }
    }

    // Ajoute des écouteurs d'événements sur tous les éléments pertinents du formulaire
    const optionsElements = form.querySelectorAll('.price-option');
    optionsElements.forEach(element => {
        element.addEventListener('change', updateEstimatedPrice);
    });

    // Calcule le prix initial au chargement de la page
    updateEstimatedPrice();
});

document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner les éléments du DOM
    const priceOptions = document.querySelectorAll('.price-option');
    const participantsSelects = document.querySelectorAll('.participants-select');
    const estimatedPriceElement = document.getElementById('estimated-price');
    const basePriceElement = document.getElementById('base-price');
    
    // Stocker les prix des options sélectionnées par défaut pour chaque étape et catégorie
    const selectedOptions = {};
    
    // Initialiser l'objet selectedOptions avec les options sélectionnées par défaut
    priceOptions.forEach(option => {
        if (option.checked) {
            const category = option.dataset.category;
            const stageId = option.dataset.stageId;
            
            if (!selectedOptions[stageId]) {
                selectedOptions[stageId] = {};
            }
            
            selectedOptions[stageId][category] = {
                price: parseFloat(option.dataset.price) || 0,
                element: option
            };
        }
    });
    
    // Initialiser le nombre de participants pour chaque étape
    participantsSelects.forEach(select => {
        const stageId = select.dataset.stageId;
        
        if (!selectedOptions[stageId]) {
            selectedOptions[stageId] = {};
        }
        
        selectedOptions[stageId]['participants'] = parseInt(select.value) || 1;
    });
    
    // Fonction pour calculer le prix total
    function calculateTotalPrice() {
        let basePrice = parseFloat(basePriceElement.value) || 0;
        let additionalPrice = 0;
        
        // Parcourir toutes les étapes et catégories d'options
        for (const stageId in selectedOptions) {
            for (const category in selectedOptions[stageId]) {
                // Ignorer la propriété 'participants'
                if (category === 'participants') continue;
                
                const option = selectedOptions[stageId][category];
                let optionPrice = option.price;
                
                // Multiplier par le nombre de participants pour les activités
                if (category === 'activites' && selectedOptions[stageId]['participants']) {
                    optionPrice *= selectedOptions[stageId]['participants'];
                }
                
                additionalPrice += optionPrice;
            }
        }
        
        // Calculer le prix total
        const totalPrice = basePrice + additionalPrice;
        
        // Formater le prix pour l'affichage (XX,XX €)
        return totalPrice.toFixed(2).replace('.', ',') + ' €';
    }
    
    // Fonction pour mettre à jour l'affichage du prix
    function updatePriceDisplay() {
        const formattedPrice = calculateTotalPrice();
        estimatedPriceElement.textContent = formattedPrice;
        
        // Animation pour mettre en évidence la mise à jour du prix
        estimatedPriceElement.classList.add('price-updated');
        setTimeout(() => {
            estimatedPriceElement.classList.remove('price-updated');
        }, 500);
    }
    
    // Ajouter des écouteurs d'événements pour les options de prix
    priceOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.checked) {
                const category = this.dataset.category;
                const stageId = this.dataset.stageId;
                const price = parseFloat(this.dataset.price) || 0;
                
                // Mettre à jour l'option sélectionnée
                if (!selectedOptions[stageId]) {
                    selectedOptions[stageId] = {};
                }
                
                selectedOptions[stageId][category] = {
                    price: price,
                    element: this
                };
                
                // Mettre à jour l'affichage du prix
                updatePriceDisplay();
            }
        });
    });
    
    // Ajouter des écouteurs d'événements pour les sélecteurs de participants
    participantsSelects.forEach(select => {
        select.addEventListener('change', function() {
            const stageId = this.dataset.stageId;
            
            if (!selectedOptions[stageId]) {
                selectedOptions[stageId] = {};
            }
            
            selectedOptions[stageId]['participants'] = parseInt(this.value) || 1;
            
            // Mettre à jour l'affichage du prix
            updatePriceDisplay();
        });
    });
    
    // Calculer et afficher le prix initial
    updatePriceDisplay();
});

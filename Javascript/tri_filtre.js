/**
 * ../js/filtering_sorting.js
 * Handles client-side filtering (by changing display) and sorting
 * for the trip list on Destination.php
 */
document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selection (keep as before) ---
    const sortSelect = document.getElementById('sort-select');
    const tripListContainer = document.getElementById('trip-list-container');
    const filterControls = document.querySelectorAll('.filter-control');
    const filterPaysSelect = document.getElementById('filter-pays');
    const filterDureeMaxInput = document.getElementById('filter-duree-max');
    const filterPrixMaxInput = document.getElementById('filter-prix-max');
    const filterGroups = document.querySelectorAll('.filter-group');

    const storageKeySort = 'finalTripSortPreference';
    // const storageKeyFilters = 'finalTripFilterPreference'; // Uncomment if saving filters later

    // --- Basic check if elements exist (keep as before) ---
    if (!sortSelect || !tripListContainer || filterControls.length === 0) {
        console.warn("Warning: Essential sorting/filtering elements might be missing on this page.");
    }

    // --- Helper Functions (getSortValue, getActiveFilters, cardMatchesFilters - keep as before) ---
    function getSortValue(element, key) {
        const value = element.dataset[key] || '';
        if (key === 'price' || key === 'duration' || key === 'stages') {
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }
        if (key === 'date') {
            return value || '9999-12-31';
        }
        return value;
    }

    function getActiveFilters() {
        const activeFilters = {
            pays: filterPaysSelect ? filterPaysSelect.value : '',
            dureeMax: filterDureeMaxInput ? (parseInt(filterDureeMaxInput.value, 10) || null) : null,
            prixMax: filterPrixMaxInput ? (parseFloat(filterPrixMaxInput.value) || null) : null,
        };
        filterGroups.forEach(group => {
            const key = group.dataset.filterKey;
            if (key) {
                activeFilters[key] = [];
                group.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
                    activeFilters[key].push(checkbox.value.toLowerCase());
                });
            }
        });
        if (activeFilters.dureeMax === 0) activeFilters.dureeMax = null;
        if (activeFilters.prixMax === 0) activeFilters.prixMax = null;
        return activeFilters;
    }

    function cardMatchesFilters(cardElement, filters) {
        const cardData = cardElement.dataset;
        if (filters.pays && cardData.pays !== filters.pays) return false;
        const cardDuration = parseFloat(cardData.duration) || 0;
        if (filters.dureeMax !== null && cardDuration > filters.dureeMax) return false;
        const cardPrice = parseFloat(cardData.price) || 0;
        if (filters.prixMax !== null && cardPrice > filters.prixMax) return false;
        for (const key in filters) {
            if (Array.isArray(filters[key]) && filters[key].length > 0) {
                const cardValues = (cardData[key] || '').split(',').map(item => item.trim().toLowerCase());
                const hasMatch = filters[key].some(filterValue => cardValues.includes(filterValue));
                if (!hasMatch) return false;
            }
        }
        return true;
    }


    /**
     * Main function to filter visibility (using display style) and then sort the visible trip cards.
     */
    function updateTripList() {
        if (!tripListContainer) return; // Exit if container not found

        const activeFilters = getActiveFilters();
        const currentSortValue = sortSelect ? sortSelect.value : 'default';
        const [sortKey, sortDirection] = currentSortValue.split('-');

        const allTripLinks = Array.from(tripListContainer.querySelectorAll('.trip-card-link'));
        const visibleTripArticles = []; // Still collect articles for sorting

        // --- Step 1: Filter ---
        // Loop through each trip link to set display style and collect visible articles
        allTripLinks.forEach(link => {
            const card = link.querySelector('.trip-card');
            if (card) {
                if (cardMatchesFilters(card, activeFilters)) {
                    // --- CHANGE HERE ---
                    link.style.display = ''; // Set to default display (usually block or inline-block)
                    // --- END CHANGE ---
                    visibleTripArticles.push(card); // Add the ARTICLE to the sort list
                } else {
                    // --- CHANGE HERE ---
                    link.style.display = 'none'; // Hide the element completely
                    // --- END CHANGE ---
                }
            } else {
                 // --- CHANGE HERE ---
                 link.style.display = 'none'; // Hide link if card is missing
                 // --- END CHANGE ---
            }
        });

         // --- Step 2: Sort ---
         // Sort only the array of ARTICLE elements that are visible
         if (currentSortValue !== 'default' && sortKey && sortDirection) {
            visibleTripArticles.sort((cardA, cardB) => {
                const valueA = getSortValue(cardA, sortKey);
                const valueB = getSortValue(cardB, sortKey);
                let comparison = 0;
                if (valueA < valueB) comparison = -1;
                else if (valueA > valueB) comparison = 1;
                return (sortDirection === 'desc' ? (comparison * -1) : comparison);
            });
         }

        // --- Step 3: Re-append ONLY the sorted visible items ---
        // Clear the container *first* to ensure correct order and remove non-visible items from flow
        // tripListContainer.innerHTML = ''; // This is efficient but can break complex event listeners if any were added directly to cards later

        // Safer alternative: Detach all, then append visible sorted
        allTripLinks.forEach(link => link.remove()); // Remove all from DOM temporarily

        // Now append ONLY the visible ones in the new sorted order
         visibleTripArticles.forEach(card => {
             if (card.parentElement && card.parentElement.classList.contains('trip-card-link')) {
                 tripListContainer.appendChild(card.parentElement); // Append the PARENT LINK
             }
         });


        // Optional: Save filter state
        // sessionStorage.setItem(storageKeyFilters, JSON.stringify(activeFilters));
    }

    // --- Function to apply saved sort preference (keep as before) ---
     function applySavedPreferences() {
        if (!sortSelect) return;
        const savedSort = sessionStorage.getItem(storageKeySort);
        if (savedSort && savedSort !== 'default') {
            const optionExists = Array.from(sortSelect.options).some(opt => opt.value === savedSort);
            if (optionExists) sortSelect.value = savedSort;
            else { sessionStorage.removeItem(storageKeySort); sortSelect.value = 'default'; }
        } else {
            sortSelect.value = 'default';
        }
        // Add filter restoration here if implemented
        updateTripList(); // Perform initial filter/sort
    }

    // --- Attach Event Listeners (keep as before) ---
    filterControls.forEach(control => {
        const eventType = (control.type === 'number' || control.type === 'text') ? 'input' : 'change';
        control.addEventListener(eventType, updateTripList);
    });
     if (sortSelect) {
         sortSelect.addEventListener('change', () => {
             sessionStorage.setItem(storageKeySort, sortSelect.value);
             updateTripList();
         });
     }

    // --- Initial Setup (keep as before) ---
    applySavedPreferences();

}); // End DOMContentLoaded

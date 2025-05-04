/**
 * ../js/filtering_sorting.js
 * Handles client-side filtering and sorting for the trip list on Destination.php
 */
document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selection ---
    const sortSelect = document.getElementById('sort-select');
    const tripListContainer = document.getElementById('trip-list-container');
    const filterControls = document.querySelectorAll('.filter-control'); // Includes inputs, selects, checkboxes
    const filterPaysSelect = document.getElementById('filter-pays');
    const filterDureeMaxInput = document.getElementById('filter-duree-max');
    const filterPrixMaxInput = document.getElementById('filter-prix-max');
    const filterGroups = document.querySelectorAll('.filter-group'); // Specifically <ul> elements holding checkboxes

    const storageKeySort = 'finalTripSortPreference';
    // const storageKeyFilters = 'finalTripFilterPreference'; // Uncomment if saving filters later

    // --- Basic check if elements exist ---
    if (!sortSelect || !tripListContainer || filterControls.length === 0) {
        console.warn("Warning: Essential sorting/filtering elements might be missing on this page.");
        // Don't return completely, maybe only sorting or filters are present
    }

    // --- Helper Functions ---

    /**
     * Safely gets and parses the SORT value from a data attribute.
     * @param {Element} element - The trip card <article> element.
     * @param {string} key - The data key suffix (e.g., 'price', 'date').
     * @returns {number|string|Date} - The parsed value for comparison.
     */
    function getSortValue(element, key) {
        // Use || '' to provide a default empty string if dataset[key] is undefined
        const value = element.dataset[key] || '';

        if (key === 'price' || key === 'duration' || key === 'stages') {
            const num = parseFloat(value);
            // Default to 0 or a very large number for descending sort if needed? 0 is safer.
            return isNaN(num) ? 0 : num;
        }
        if (key === 'date') {
            // Treat missing/invalid dates as very far in the future for ascending sort
            return value || '9999-12-31';
        }
        // Includes pays, climat, terrain etc. - compare as strings
        return value;
    }

    /**
     * Gets the current state of all active filters from the form controls.
     * @returns {object} An object containing active filter values.
     */
    function getActiveFilters() {
        const activeFilters = {
            // Get value from select, default to empty string if not found or no value
            pays: filterPaysSelect ? filterPaysSelect.value : '',
            // Get value from number input, parse as int, default to null if empty/invalid
            dureeMax: filterDureeMaxInput ? (parseInt(filterDureeMaxInput.value, 10) || null) : null,
            // Get value from number input, parse as float, default to null if empty/invalid
            prixMax: filterPrixMaxInput ? (parseFloat(filterPrixMaxInput.value) || null) : null,
        };

        // Populate checkbox groups (climat, terrain, couchage, restrictions)
        filterGroups.forEach(group => {
            const key = group.dataset.filterKey; // Reads data-filter-key="climat", etc.
            if (key) {
                activeFilters[key] = []; // Initialize empty array
                group.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
                    // Add the value of each checked checkbox (ensure values are lowercase if needed)
                    activeFilters[key].push(checkbox.value.toLowerCase());
                });
            }
        });

        // Clean up null filters if they are 0 (user might type 0 for price/duration)
        if (activeFilters.dureeMax === 0) activeFilters.dureeMax = null;
        if (activeFilters.prixMax === 0) activeFilters.prixMax = null;


        return activeFilters;
    }

    /**
     * Checks if a trip card element matches the currently active filters.
     * @param {Element} cardElement - The trip card <article> element.
     * @param {object} filters - The object returned by getActiveFilters().
     * @returns {boolean} - True if the card matches all active filters, false otherwise.
     */
    function cardMatchesFilters(cardElement, filters) {
        const cardData = cardElement.dataset; // Access all data-* attributes

        // --- Check each filter ---

        // 1. Pays (Select dropdown)
        // If a country filter is set AND it doesn't match the card's country, return false.
        if (filters.pays && cardData.pays !== filters.pays) {
            // console.log(`Card ${cardData.id} failed Pays: ${cardData.pays} !== ${filters.pays}`);
            return false;
        }

        // 2. Durée Max (Number input)
        // If a max duration is set AND the card's duration is greater, return false.
        const cardDuration = parseFloat(cardData.duration) || 0; // Ensure numeric comparison
        if (filters.dureeMax !== null && cardDuration > filters.dureeMax) {
             // console.log(`Card ${cardData.id} failed Durée: ${cardDuration} > ${filters.dureeMax}`);
            return false;
        }

        // 3. Prix Max (Number input)
        // If a max price is set AND the card's price is greater, return false.
        const cardPrice = parseFloat(cardData.price) || 0; // Ensure numeric comparison
        if (filters.prixMax !== null && cardPrice > filters.prixMax) {
            // console.log(`Card ${cardData.id} failed Prix: ${cardPrice} > ${filters.prixMax}`);
            return false;
        }

        // 4. Checkbox Groups (Climat, Terrain, Couchage, Restrictions)
        for (const key in filters) {
            // Is this key one of our checkbox filter groups? (Check if it's an Array in activeFilters)
            // AND are there actually any checkboxes checked in this group?
            if (Array.isArray(filters[key]) && filters[key].length > 0) {
                // Get the corresponding data from the card (e.g., data-climat, data-couchage)
                // Split comma-separated values (like for couchage/restrictions) into an array. Trim whitespace.
                const cardValues = (cardData[key] || '').split(',').map(item => item.trim().toLowerCase());

                // Check if *at least one* of the filter's selected values exists in the card's values for this key.
                const hasMatch = filters[key].some(filterValue => cardValues.includes(filterValue));

                // If *none* of the required filter values are found on the card for this category, the card fails.
                if (!hasMatch) {
                    // console.log(`Card ${cardData.id} failed ${key}: Card has [${cardValues.join(',')}] but needed one of [${filters[key].join(',')}]`);
                    return false;
                }
            }
        }

        // If the card passed all the above checks, it's a match!
        return true;
    }


    /**
     * Main function to filter visibility and then sort the visible trip cards.
     */
    function updateTripList() {
        if (!tripListContainer) return; // Exit if container not found

        const activeFilters = getActiveFilters();
        const currentSortValue = sortSelect ? sortSelect.value : 'default'; // Handle missing sort select
        const [sortKey, sortDirection] = currentSortValue.split('-');

        const allTripLinks = Array.from(tripListContainer.querySelectorAll('.trip-card-link')); // Get the parent <a> links
        const visibleTripArticles = []; // Collect only the <article> elements that pass filters

        // --- Step 1: Filter ---
        // Loop through each trip link to determine visibility based on filters
        allTripLinks.forEach(link => {
            const card = link.querySelector('.trip-card'); // Get the article inside the link
            if (card) { // Ensure the card element exists
                if (cardMatchesFilters(card, activeFilters)) {
                    link.classList.remove('hidden'); // Show the link
                    visibleTripArticles.push(card); // Add the ARTICLE to the sort list
                } else {
                    link.classList.add('hidden'); // Hide the link
                }
            } else {
                link.classList.add('hidden'); // Hide link if card is missing somehow
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
         // else: Keep the original DOM order for filtered items if sort is 'default'

        // --- Step 3: Re-append to DOM ---
        // Append the visible, sorted items first. This moves them to the correct order at the top.
         visibleTripArticles.forEach(card => {
             // Append the PARENT LINK element (which contains the sorted article)
             if (card.parentElement && card.parentElement.classList.contains('trip-card-link')) {
                 tripListContainer.appendChild(card.parentElement);
             }
         });
         // Append the hidden items afterwards (they remain hidden but are moved to the end).
         allTripLinks.forEach(link => {
             if (link.classList.contains('hidden')) {
                 tripListContainer.appendChild(link);
             }
         });

        // Optional: Save filter state
        // sessionStorage.setItem(storageKeyFilters, JSON.stringify(activeFilters));
    }

    /**
     * Applies saved sort preference on page load.
     * (Could be extended to apply saved filters too).
     */
    function applySavedPreferences() {
        if (!sortSelect) return; // Don't try if sort select doesn't exist

        // Apply Sort Preference
        const savedSort = sessionStorage.getItem(storageKeySort);
        if (savedSort && savedSort !== 'default') {
            const optionExists = Array.from(sortSelect.options).some(opt => opt.value === savedSort);
            if (optionExists) {
                sortSelect.value = savedSort; // Set dropdown to saved value
            } else {
                 sessionStorage.removeItem(storageKeySort); // Clean up invalid saved value
                 sortSelect.value = 'default';
            }
        } else {
            sortSelect.value = 'default'; // Ensure default is selected if nothing saved
        }

        // Apply Filter Preferences (Example - uncomment and adapt if saving filters)
        /*
        const savedFiltersJSON = sessionStorage.getItem(storageKeyFilters);
        if (savedFiltersJSON) {
            try {
                const savedFilters = JSON.parse(savedFiltersJSON);
                // Restore select
                if (filterPaysSelect && savedFilters.pays) filterPaysSelect.value = savedFilters.pays;
                // Restore number inputs
                if (filterDureeMaxInput && savedFilters.dureeMax) filterDureeMaxInput.value = savedFilters.dureeMax;
                if (filterPrixMaxInput && savedFilters.prixMax) filterPrixMaxInput.value = savedFilters.prixMax;
                // Restore checkboxes
                filterGroups.forEach(group => {
                    const key = group.dataset.filterKey;
                    if (key && Array.isArray(savedFilters[key])) {
                        group.querySelectorAll('.filter-checkbox').forEach(checkbox => {
                            checkbox.checked = savedFilters[key].includes(checkbox.value.toLowerCase());
                        });
                    }
                });
            } catch (e) {
                console.error("Error parsing saved filters:", e);
                sessionStorage.removeItem(storageKeyFilters); // Clear corrupted data
            }
        }
        */

        // Perform the initial filter and sort based on loaded/default values
        updateTripList();
    }

    // --- Attach Event Listeners ---
    // Add listeners to all filter controls
    filterControls.forEach(control => {
        const eventType = (control.type === 'number' || control.type === 'text') ? 'input' : 'change';
        control.addEventListener(eventType, updateTripList);
    });

     // Listener for the sort dropdown (also saves the preference)
     if (sortSelect) {
         sortSelect.addEventListener('change', () => {
             sessionStorage.setItem(storageKeySort, sortSelect.value); // Save preference
             updateTripList(); // Update the list
         });
     }

    // --- Initial Setup ---
    applySavedPreferences(); // Apply saved sort/filters and run initial update

}); // End DOMContentLoaded

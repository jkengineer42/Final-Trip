document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selection ---
    const sortSelect = document.getElementById('sort-select');
    const tripListContainer = document.getElementById('trip-list-container');
    // Select all filter controls (checkboxes, selects, number inputs)
    const filterControls = document.querySelectorAll('.filter-control');
    // Get specific filter inputs by ID
    const filterPaysSelect = document.getElementById('filter-pays');
    const filterDureeMaxInput = document.getElementById('filter-duree-max');
    const filterPrixMaxInput = document.getElementById('filter-prix-max');
    // Get checkbox groups
    const filterGroups = document.querySelectorAll('.filter-group');

    const storageKeySort = 'finalTripSortPreference';
    const storageKeyFilters = 'finalTripFilterPreference'; // For potentially saving filters

    // --- Basic check if elements exist ---
    if (!sortSelect || !tripListContainer || filterControls.length === 0) {
        console.error("Essential sorting/filtering elements not found!");
        return;
    }

    // --- Helper Functions ---

    /**
     * Safely gets and parses SORT value from data attribute.
     */
    function getSortValue(element, key) {
        const value = element.dataset[key];
        if (!value) { // Handle missing data attributes gracefully
             if (key === 'price' || key === 'duration' || key === 'stages') return 0;
             if (key === 'date') return '9999-12-31';
             return '';
        }
        if (key === 'price' || key === 'duration' || key === 'stages') {
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }
        if (key === 'date') {
            return value; // String comparison works for YYYY-MM-DD
        }
        return value;
    }

    /**
     * Gets the current state of all active filters.
     * @returns {object} An object containing active filter values.
     */
    function getActiveFilters() {
        const activeFilters = {
            pays: filterPaysSelect ? filterPaysSelect.value : '',
            dureeMax: filterDureeMaxInput ? (parseInt(filterDureeMaxInput.value, 10) || null) : null,
            prixMax: filterPrixMaxInput ? (parseFloat(filterPrixMaxInput.value) || null) : null,
            // Initialize checkbox groups
        };
        // Populate checkbox groups
        filterGroups.forEach(group => {
            const key = group.dataset.filterKey;
            if (key) {
                activeFilters[key] = []; // Initialize empty array for this group
                group.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
                    activeFilters[key].push(checkbox.value);
                });
            }
        });

        return activeFilters;
    }

    /**
     * Checks if a trip card matches the currently active filters.
     * @param {Element} cardElement - The trip card <article> element.
     * @param {object} filters - The object returned by getActiveFilters().
     * @returns {boolean} - True if the card matches, false otherwise.
     */
    function cardMatchesFilters(cardElement, filters) {
        const cardData = cardElement.dataset;

        // 1. Check Country (if filter selected)
        if (filters.pays && cardData.pays !== filters.pays) {
            return false;
        }

        // 2. Check Max Duration (if filter set)
        if (filters.dureeMax !== null && (parseFloat(cardData.duration) || 0) > filters.dureeMax) {
            return false;
        }

        // 3. Check Max Price (if filter set)
        if (filters.prixMax !== null && (parseFloat(cardData.price) || 0) > filters.prixMax) {
            return false;
        }

        // 4. Check Checkbox Groups (Climat, Terrain, Couchage, Restrictions)
        for (const key in filters) {
            // Check if it's one of our checkbox group keys AND if any checkboxes are checked for that group
            if (Array.isArray(filters[key]) && filters[key].length > 0) {
                 // Card must have at least one of the selected values for this category
                 const cardValues = (cardData[key] || '').split(','); // Get values from card's data-attribute
                 const hasMatch = filters[key].some(filterValue => cardValues.includes(filterValue.toLowerCase())); // Use toLowerCase if values might differ in case

                 if (!hasMatch) {
                    return false; // If no match found for this checked group, the card fails
                 }
            }
        }

        // If all checks passed
        return true;
    }


    /**
     * Updates the visibility and order of trip cards based on filters and sort.
     */
    function updateTripList() {
        const activeFilters = getActiveFilters();
        const currentSortValue = sortSelect.value;
        const [sortKey, sortDirection] = currentSortValue.split('-');

        const allTripLinks = Array.from(tripListContainer.querySelectorAll('.trip-card-link')); // Get the <a> elements
        const visibleTripArticles = []; // Store only articles that pass filters

        // Step 1: Filter - Set visibility and collect visible articles
        allTripLinks.forEach(link => {
            const card = link.querySelector('.trip-card'); // Find the article inside the link
            if (card && cardMatchesFilters(card, activeFilters)) {
                link.classList.remove('hidden'); // Show the link (and its card)
                visibleTripArticles.push(card); // Add the article element to the list for sorting
            } else {
                link.classList.add('hidden'); // Hide the link (and its card)
            }
        });

         // Step 2: Sort - Sort only the *visible* articles based on current sort selection
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
         // else: keep the default order for visible items

        // Step 3: Re-append - Append the sorted visible items first, then the hidden ones
        // This moves the visible, sorted items to the top of the container in the correct order.
         visibleTripArticles.forEach(card => {
             if (card.parentElement && card.parentElement.classList.contains('trip-card-link')) {
                 tripListContainer.appendChild(card.parentElement); // Append the parent link
             }
         });
         // Append hidden items afterwards (they keep their hidden class)
         allTripLinks.forEach(link => {
             if (link.classList.contains('hidden')) {
                 tripListContainer.appendChild(link);
             }
         });


        // Optional: Save filters to sessionStorage if needed (similar to sorting)
        // sessionStorage.setItem(storageKeyFilters, JSON.stringify(activeFilters));
    }

    // --- Function to apply saved sort preference (slightly modified) ---
    function applySavedPreferences() {
        // Apply Sort
        const savedSort = sessionStorage.getItem(storageKeySort);
        if (savedSort && savedSort !== 'default') {
            const optionExists = Array.from(sortSelect.options).some(opt => opt.value === savedSort);
            if (optionExists) {
                sortSelect.value = savedSort;
            } else {
                 sessionStorage.removeItem(storageKeySort);
                 sortSelect.value = 'default';
            }
        } else {
            sortSelect.value = 'default';
        }

        // Apply Filters (if you choose to save them)
        // const savedFilters = JSON.parse(sessionStorage.getItem(storageKeyFilters) || '{}');
        // TODO: Loop through savedFilters and set the values of the corresponding filter controls
        // Example: if (savedFilters.pays) filterPaysSelect.value = savedFilters.pays; etc.

        // Initial update based on loaded preferences (or defaults)
        updateTripList();
    }

    // --- Attach Event Listeners ---
    // Listen to changes on ANY filter control
    filterControls.forEach(control => {
        control.addEventListener('change', updateTripList);
         // Use 'input' for number fields for more responsiveness
         if (control.type === 'number') {
             control.addEventListener('input', updateTripList);
         }
    });

     // Also listen to the sort select (which now just calls the main update function)
     sortSelect.addEventListener('change', () => {
         sessionStorage.setItem(storageKeySort, sortSelect.value); // Save sort choice
         updateTripList();
     });


    // --- Apply saved preferences and initial sort/filter on page load ---
    applySavedPreferences(); // This will call updateTripList() internally

}); // End DOMContentLoaded

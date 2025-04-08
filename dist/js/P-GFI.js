function executeGererFichesJavaScript() {
    console.log("Initializing fiche intervention management script...");

    // 1. Tab Switching Functionality
    const tabs = document.querySelectorAll('.tab');
    const tableRows = document.querySelectorAll('table tr.tr-body');

    function switchTab(activeTab) {
        tabs.forEach(tab => {
            tab.classList.remove('text-[#0455b7]', 'bg-white', 'rounded-lg');
            tab.classList.add('text-gray-600', 'rounded-xl');
        });

        activeTab.classList.remove('text-gray-600', 'rounded-xl');
        activeTab.classList.add('text-[#0455b7]', 'bg-white', 'rounded-lg');

        tableRows.forEach(row => {
            const archiveCell = row.querySelector('.archive-status');
            if (!archiveCell) return;

            const archiveStatus = archiveCell.textContent.trim().toLowerCase();
            let shouldShow = false;

            switch (activeTab.id) {
                case 'allTab':
                    shouldShow = true;
                    break;
                case 'archivedTab':
                    shouldShow = archiveStatus.includes('archivée');
                    break;
                case 'activeTab':
                    shouldShow = !archiveStatus.includes('archivée');
                    break;
            }

            row.style.display = shouldShow ? 'table-row' : 'none';
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab));
    });

    // Initialize with first tab active
    if (tabs.length > 0) switchTab(tabs[0]);

    // 2. Checkbox Selection
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="select-fiche"]:not(#select-all)');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // 3. Search Functionality
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (!searchTerm) {
            const activeTab = document.querySelector('.tab.bg-white') || tabs[0];
            if (activeTab) switchTab(activeTab);
            document.getElementById('no-results-message')?.remove();
            return;
        }

        let hasResults = false;
        tableRows.forEach(row => {
            let rowText = '';
            row.querySelectorAll('td').forEach(cell => {
                rowText += cell.textContent.toLowerCase() + ' ';
            });

            if (rowText.includes(searchTerm)) {
                row.style.display = 'table-row';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultsMsg = document.getElementById('no-results-message');
        if (!hasResults) {
            if (!noResultsMsg) {
                const msg = document.createElement('div');
                msg.id = 'no-results-message';
                msg.className = 'text-center py-4 text-gray-500';
                msg.textContent = `Aucun résultat trouvé pour "${searchTerm}"`;
                document.querySelector('table').after(msg);
            }
        } else {
            noResultsMsg?.remove();
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performSearch();
        });
    }

    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }

    // 4. Export Dropdown Functionality
    const exportButton = document.getElementById('export-button');
    const exportDropdown = document.getElementById('export-dropdown');
    
    if (exportButton && exportDropdown) {
        exportButton.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
            if (!exportButton.contains(e.target) && !exportDropdown.contains(e.target)) {
                exportDropdown.classList.add('hidden');
            }
        });
    }

    // 5. Action Dropdown Functionality
    $(document).on('click', '[data-dropdown-toggle="dropdownDots"]', function() {
        const dropdown = $(this).next('.z-10');
        if (dropdown.hasClass('hidden')) {
            $('.z-10').addClass('hidden');
            dropdown.removeClass('hidden');
            
            const button = $(this);
            const buttonRect = button[0].getBoundingClientRect();
            dropdown.css({
                'top': buttonRect.bottom + window.scrollY + 'px',
                'left': buttonRect.left + window.scrollX - dropdown.width() + button.width() + 'px'
            });
        } else {
            dropdown.addClass('hidden');
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).is('[data-dropdown-toggle="dropdownDots"]') && 
            !$(e.target).parents('[data-dropdown-toggle="dropdownDots"]').length) {
            $('.z-10').addClass('hidden');
        }
    });

    // 6. Archive Status Functionality
    function showArchiveStatus(cell) {
        const list = cell.querySelector('.archive-list');
        if (!list) return;
        
        // Close all other archive lists first
        document.querySelectorAll('.archive-list').forEach(el => {
            if (el !== list) el.classList.add('hidden');
        });
        
        // Set the current value in the dropdown
        const currentStatus = cell.querySelector('.archive-status').textContent.trim();
        const select = list.querySelector('select');
        select.value = currentStatus.includes('Archivée') ? '1' : '0';
        
        // Position and show the list
        const cellRect = cell.getBoundingClientRect();
        list.style.top = `${cellRect.bottom + window.scrollY}px`;
        list.style.left = `${cellRect.left + window.scrollX}px`;
        list.classList.remove('hidden');
    }

    function hideAllArchiveLists() {
        document.querySelectorAll('.archive-list').forEach(list => {
            list.classList.add('hidden');
        });
    }

    // Double click to show archive list
    document.querySelectorAll('.etat-cell').forEach(cell => {
        cell.addEventListener('dblclick', function(e) {
            e.stopPropagation();
            showArchiveStatus(this);
        });
    });

    // Save archive status change
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('save-archive')) {
            const list = e.target.closest('.archive-list');
            if (!list) return;
            
            const select = list.querySelector('select');
            const newStatus = select.value;
            const cell = list.closest('.etat-cell');
            const statusText = cell.querySelector('.archive-status');
            
            // Update UI
            statusText.textContent = newStatus === '1' ? 'Archivée' : 'Active';
            list.classList.add('hidden');
            
            // AJAX request to update the database
            const ficheNum = cell.closest('tr').dataset.ficheNum;
            console.log(`Updating fiche ${ficheNum} to archive status: ${newStatus}`);
            
            // Example AJAX call:
            fetch('../app/update_fiche_archive.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fiche_num: ficheNum,
                    archived: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert UI if update failed
                    statusText.textContent = newStatus === '1' ? 'Active' : 'Archivée';
                    alert('Erreur lors de la mise à jour: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusText.textContent = newStatus === '1' ? 'Active' : 'Archivée';
            });
        }
        
        // Cancel button
        if (e.target.classList.contains('cancel-archive')) {
            const list = e.target.closest('.archive-list');
            if (list) list.classList.add('hidden');
        }
    });

    // Close archive lists when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.etat-cell') && !e.target.closest('.archive-list')) {
            hideAllArchiveLists();
        }
    });

    // 7. Confirmation for Delete Actions
    window.confirmDelete = function() {
        return confirm('Êtes-vous sûr de vouloir supprimer cette fiche d\'intervention ?');
    };

    console.log("Fiche intervention management script initialized successfully");
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', executeGererFichesJavaScript);

// For AJAX loaded content
function reinitializeGererFiches() {
    executeGererFichesJavaScript();
}

executeGererFichesJavaScript();
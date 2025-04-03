function executeManagUsersJavaScript() {
    console.log("Initializing user management script...");

    // 1. Tab Switching Functionality
    const tabs = document.querySelectorAll('.tab');
    const tableRows = document.querySelectorAll('table tr:not(.tr-head)');

    function switchTab(activeTab) {
        tabs.forEach(tab => {
            tab.classList.remove('text-[#0455b7]', 'bg-white', 'rounded-lg');
            tab.classList.add('text-gray-600', 'rounded-xl');
            tab.setAttribute('aria-selected', 'false');
        });

        activeTab.classList.remove('text-gray-600', 'rounded-xl');
        activeTab.classList.add('text-[#0455b7]', 'bg-white', 'rounded-lg');
        activeTab.setAttribute('aria-selected', 'true');

        const selectedRole = activeTab.dataset.role || 'all';

        tableRows.forEach(row => {
            if (row.cells.length > 5) {
                const role = row.cells[5].textContent.trim().toLowerCase();
                const roleToMatch = selectedRole.toLowerCase();
                
                if (selectedRole === 'all' || role.includes(roleToMatch)) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            console.log(`Switching to tab: ${tab.dataset.role}`);
            switchTab(tab);
        });
    });

    // Initialize first tab as active
    if (tabs.length > 0) {
        switchTab(tabs[0]);
    }

    // 2. Checkbox Selection
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('input[name="select-user"]');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            console.log(`Select all checked: ${this.checked}`);
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // 3. Account Status Toggle
    document.querySelectorAll('input[id^="account-status-"]').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const newStatus = this.checked ? 1 : 0;
            console.log(`Changing status for user ${userId} to ${newStatus}`);

            // Update UI immediately
            const statusText = document.getElementById(`status-text-${userId}`);
            if (statusText) {
                statusText.textContent = newStatus ? 'Activé' : 'Désactivé';
            }

            // Send request to server
            const endpoint = newStatus ? 'enable_user.php' : 'disable_user.php';
            window.location.href = `gerer_les_comptes/${endpoint}?id=${userId}`;
        });
    });

    // 4. Modal Functionality
    const modalOverlay = document.getElementById('modal-overlay-M');
    const modal = document.getElementById('modal');
    const closeModalButton = document.getElementById('close-modal');
    const modifyButtons = document.querySelectorAll('.modify-user-btn');

    function showModal() {
        console.log("Showing modal");
        modalOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        console.log("Hiding modal");
        modalOverlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    modifyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const username = this.dataset.username;
            const nom = this.dataset.nom;
            const prenom = this.dataset.prenom;
            const email = this.dataset.email;
            const roleId = this.dataset.roleId;

            console.log(`Editing user ${userId}`);

            // Fill form fields
            document.getElementById('user_id').value = userId;
            document.getElementById('nom').value = nom;
            document.getElementById('prenom').value = prenom;
            document.getElementById('email').value = email;

            // Set role display
            const roleName = roleId == 1 ? 'Admin' : 
                           roleId == 2 ? 'Technicien' : 'Receveur';
            document.getElementById('account_type').textContent = 
                `Type de compte est : ${roleName}`;
            document.getElementById('role_id').value = roleId;

            showModal();
        });
    });

    closeModalButton.addEventListener('click', hideModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            hideModal();
        }
    });

    // 5. Search Functionality
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        console.log(`Searching for: ${searchTerm}`);

        if (!searchTerm) {
            // Reset to current tab view if search is empty
            const activeTab = document.querySelector('.tab[aria-selected="true"]') || tabs[0];
            switchTab(activeTab);
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

        // Show no results message if needed
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

    searchInput.addEventListener('input', performSearch);
    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // 6. Export Functionality
    const exportButton = document.getElementById('export-button');
    const exportDropdown = document.getElementById('export-dropdown');

    if (exportButton && exportDropdown) {
        exportButton.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!exportButton.contains(e.target)) {
                exportDropdown.classList.add('hidden');
            }
        });

        // Update export links with selected users
        document.querySelectorAll('#export-dropdown a').forEach(link => {
            link.addEventListener('click', function() {
                const selectedUsers = [];
                document.querySelectorAll('input[name="select-user"]:checked').forEach(checkbox => {
                    selectedUsers.push(checkbox.id.replace('select-user-', ''));
                });

                // If none selected, export all
                if (selectedUsers.length === 0) {
                    document.querySelectorAll('input[name="select-user"]').forEach(checkbox => {
                        selectedUsers.push(checkbox.id.replace('select-user-', ''));
                    });
                }

                // Add users to URL
                const url = new URL(this.href);
                url.searchParams.set('users', selectedUsers.join(','));
                this.href = url.toString();
                console.log(`Exporting users: ${selectedUsers.join(', ')}`);
            });
        });
    }

    console.log("User management script initialized successfully");
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    executeManagUsersJavaScript();
});

// For AJAX loaded content
function reinitializeManagUsers() {
    console.log("Reinitializing user management script");
    executeManagUsersJavaScript();
}

executeManagUsersJavaScript();

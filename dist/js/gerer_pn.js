
function executeGererPnJavaScript() {
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
            const etatCell = row.querySelector('.etat-text');
            if (!etatCell) return;

            const etat = etatCell.textContent.trim().toLowerCase();

            switch (activeTab.id) {
                case 'allTab':
                    row.style.display = 'table-row';
                    break;
                case 'nouveauTab':
                    row.style.display = etat.includes('nouveau') ? 'table-row' : 'none';
                    break;
                case 'enCoursTab':
                    row.style.display = etat.includes('en cours') ? 'table-row' : 'none';
                    break;
                case 'resoluTab':
                    row.style.display = etat.includes('résolu') ? 'table-row' : 'none';
                    break;
                case 'fermeTab':
                    row.style.display = etat.includes('fermé') ? 'table-row' : 'none';
                    break;
                default:
                    row.style.display = 'none';
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab));
    });

    const defaultTab = document.getElementById('allTab');
    if (defaultTab) {
        switchTab(defaultTab);
    }

    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('table input[type="checkbox"]:not(#select-all)');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('click', function () {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    // Get all dropdown buttons
    const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');

    // Add event listener to each dropdown button
        dropdownButtons.forEach(button => {
            button.addEventListener('click', () => {
            // Get the dropdown menu
            const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));

            // Toggle the dropdown menu
            dropdownMenu.classList.toggle('hidden');
        });
    });


    const modalOverlay = document.getElementById('modal-overlay');
    const modal = document.getElementById('modal');
    
    // Add click event listener to all elements with the class 'open-rp'
    document.querySelectorAll('.open-rp').forEach(function (element) {
        element.addEventListener('click', function () {
            const rapNum = this.textContent.trim(); // Get the rap_num from the clicked cell
            console.log('Clicked rap_num:', rapNum); // Debugging: Check if rap_num is correct
    
            // Fetch the rapport data
            fetch(`get_rapport.php?rap_num=${rapNum}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched data:', data); // Debugging: Check fetched data
                    if (data) {
                        // Populate the modal with the fetched data
                        document.getElementById('modal-panne-num').textContent = data.panne_num || 'N/A';
                        document.getElementById('modal-rap-num').textContent = data.rap_num || 'N/A';
                        document.getElementById('modal-panne-name').textContent = data.panne_name || 'N/A';
                        document.getElementById('modal-date-signalement').textContent = data.date_signalement || 'N/A';
                        document.getElementById('modal-etablissement-name').textContent = data.etablissement_name || 'N/A';
                        document.getElementById('modal-type-name').textContent = data.type_name || 'N/A';
                        document.getElementById('modal-panne-etat').textContent = data.panne_etat || 'N/A';
                        document.getElementById('modal-rap-date').textContent = data.rap_date || 'N/A';
                        document.getElementById('modal-user-nom').textContent = data.user_nom || 'N/A';
                        document.getElementById('modal-user-prenom').textContent = data.user_prenom || 'N/A';
    
                        // Show the modal
                        modalOverlay.classList.remove('hidden');
                    } else {
                        console.error('No data received');
                    }
                })
                .catch(error => console.error('Error fetching rapport data:', error));
        });
    });
    
    // Close modal when clicking the close button or outside the modal
    document.getElementById('close-modal').addEventListener('click', function () {
        modalOverlay.classList.add('hidden');
    });
    
    modalOverlay.addEventListener('click', function (event) {
        if (event.target === modalOverlay) {
            modalOverlay.classList.add('hidden');
        }
    });

}

// Execute the function when the script is loaded
executeGererPnJavaScript();
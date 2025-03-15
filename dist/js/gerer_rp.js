
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

}

// Execute the function when the script is loaded
executeGererPnJavaScript();
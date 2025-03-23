function executeGererPnJavaScript() {
    const tabs = document.querySelectorAll('.tab');
    const tableRows = document.querySelectorAll('table tr.tr-body');
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('table input[type="checkbox"]:not(#select-all)');
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');

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


    // Selectors
    const modalOverlay = document.getElementById('modal-overlay');
    const modal = document.getElementById('modal');
    const closeModalButton = document.getElementById('close-modal');

    // Function to populate the modal with data
    function populateModal(data) {
        if (!data) {
            console.error('No data received');
            return;
        }

        // Populate modal fields
        document.getElementById('modal-panne-num').textContent = data.panneNum || 'N/A';
        document.getElementById('modal-rap-num').textContent = data.rapNum || 'N/A';
        document.getElementById('modal-panne-name').textContent = data.panneName || 'N/A';
        document.getElementById('modal-date-signalement').textContent = data.dateSignalement || 'N/A';
        document.getElementById('modal-etablissement-name').textContent = data.etablissementName || 'N/A';
        document.getElementById('modal-type-name').textContent = data.typeName || 'N/A';
        document.getElementById('modal-panne-etat').textContent = data.panneEtat || 'N/A';
        document.getElementById('modal-rap-date').textContent = data.rapDate || 'N/A';
        document.getElementById('modal-user-nom').textContent = data.userNom || 'N/A';
        document.getElementById('modal-user-prenom').textContent = data.userPrenom || 'N/A';

        // Show the modal
        modalOverlay.classList.remove('hidden');
    }

    // Function to handle opening the modal
    function handleOpenModal(event) {
        // Get the closest row (<tr>) to the clicked cell
        const row = event.target.closest('tr');

        // Extract data from data-attributes
        const data = {
            panneNum: row.getAttribute('data-panne-num'),
            panneName: row.getAttribute('data-panne-name'),
            dateSignalement: row.getAttribute('data-date-signalement'),
            etablissementName: row.getAttribute('data-etablissement-name'),
            typeName: row.getAttribute('data-type-name'),
            panneEtat: row.getAttribute('data-panne-etat'),
            rapNum: row.getAttribute('data-rap-num'),
            rapDate: row.getAttribute('data-rap-date'),
            userNom: row.getAttribute('data-user-nom'),
            userPrenom: row.getAttribute('data-user-prenom')
        };

        // Populate and show the modal
        populateModal(data);
    }

    // Function to close the modal
    function closeModal() {
        modalOverlay.classList.add('hidden');
    }

    // Add event listeners to all cells in the table rows
    document.querySelectorAll('.tr-body td').forEach(cell => {
        cell.addEventListener('click', handleOpenModal);
    });

    // Close modal when clicking the close button or outside the modal
    closeModalButton.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    // تعريف وظيفة البحث
    function performSearch() {
        const searchText = searchInput.value.toLowerCase();
        
        if (searchText.trim() === '') {
            // إذا كان حقل البحث فارغًا، نعود إلى الحالة الافتراضية للتبويب النشط
            const activeTab = document.querySelector('.tab.bg-white');
            if (activeTab) {
                switchTab(activeTab);
            }
            
            const noResultsMessage = document.getElementById('no-results-message');
            if (noResultsMessage) {
                noResultsMessage.remove();
            }
            return;
        }
        
        // البحث في جميع الخلايا
        tableRows.forEach(row => {
            let found = false;
            const cells = row.querySelectorAll('td');
            
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchText)) {
                    found = true;
                }
            });
            
            row.style.display = found ? 'table-row' : 'none';
        });
        
        // عرض رسالة إذا لم يكن هناك نتائج
        const visibleRows = document.querySelectorAll('table tr.tr-body[style="display: table-row;"]');
        let noResultsMessage = document.getElementById('no-results-message');
        
        if (visibleRows.length === 0) {
            if (!noResultsMessage) {
                noResultsMessage = document.createElement('div');
                noResultsMessage.id = 'no-results-message';
                noResultsMessage.className = 'text-center py-4';
                document.querySelector('table').after(noResultsMessage);
            }
            noResultsMessage.textContent = `Aucun résultat trouvé pour "${searchText}".`;
        } else if (noResultsMessage) {
            noResultsMessage.remove();
        }
        
        // إلغاء تحديد "تحديد الكل" عند البحث
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
    }

    // إضافة معالجات الأحداث مرة واحدة فقط
    if (searchInput) {
        // تفعيل البحث التلقائي بمجرد الكتابة
        searchInput.addEventListener('input', performSearch);
        
        // الاحتفاظ بمعالج حدث الضغط على Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault(); // منع إرسال النموذج إذا كان داخل نموذج
                performSearch();
            }
        });
    }
    
    if (searchButton) {
        // الاحتفاظ بمعالج حدث النقر على زر البحث
        searchButton.addEventListener('click', performSearch);
    }
    
    // دالة تأكيد الحذف
    window.confirmDelete = function() {
        return confirm('Are you sure you want to delete this panne?');
    };
}

// تنفيذ الوظيفة عند تحميل البرنامج النصي
executeGererPnJavaScript();
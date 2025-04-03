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

$(document).on('click', '[data-dropdown-toggle="dropdownDots"]', function() {
    const dropdown = $(this).next('.z-10');
    if (dropdown.hasClass('hidden')) {
    // Close all open drop-down lists first
        $('.z-10').addClass('hidden');
        // Then open the current drop-down menu
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

// Close the drop-down menu when clicking anywhere else
$(document).on('click', function(e) {
    if (!$(e.target).is('[data-dropdown-toggle="dropdownDots"]') && !$(e.target).parents('[data-dropdown-toggle="dropdownDots"]').length) {
        $('.z-10').addClass('hidden');
    }
});
    

    // Export dropdown functionality
    const exportButton = document.getElementById('export-button');
    const exportDropdown = document.getElementById('export-dropdown');
    
    exportButton.addEventListener('click', function() {
        exportDropdown.classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!exportButton.contains(event.target) && !exportDropdown.contains(event.target)) {
            exportDropdown.classList.add('hidden');
        }
    });
    
    // Existing JavaScript for user management
    // ... (keep any existing JavaScript here)
    
    // Handle action dropdown buttons
document.addEventListener('DOMContentLoaded', function() {
    // Initialize action dropdowns
    const actionButtons = document.querySelectorAll('[data-dropdown-toggle="dropdownDots"]');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.absolute.z-10').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
            
            // Position the dropdown
            const rect = button.getBoundingClientRect();
            dropdown.style.top = `${rect.bottom + window.scrollY}px`;
            dropdown.style.left = `${rect.left + window.scrollX}px`;
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.absolute.z-10').forEach(menu => {
            menu.classList.add('hidden');
        });
    });
});

// Handle main export button
document.addEventListener('DOMContentLoaded', function() {
    const exportButton = document.getElementById('export-button');
    const exportDropdown = document.getElementById('export-dropdown');
    
    if (exportButton && exportDropdown) {
        exportButton.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('hidden');
            
            // Position the dropdown
            const rect = exportButton.getBoundingClientRect();
            exportDropdown.style.top = `${rect.bottom + window.scrollY}px`;
            exportDropdown.style.left = `${rect.left + window.scrollX}px`;
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!exportButton.contains(e.target) && !exportDropdown.contains(e.target)) {
                exportDropdown.classList.add('hidden');
            }
        });
    }
});
executeGererPnJavaScript();
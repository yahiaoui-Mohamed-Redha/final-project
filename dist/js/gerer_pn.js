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
executeGererPnJavaScript();
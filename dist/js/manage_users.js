function executeManagUsersJavaScript() {
    const tabs = document.querySelectorAll('.tab');
    const tableRows = document.querySelectorAll('table tr:not(:first-child)');
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('table input[type="checkbox"]:not(#select-all)');

    function switchTab(activeTab) {
        tabs.forEach(tab => {
            tab.classList.remove('text-[#0455b7]', 'bg-white', 'rounded-lg');
            tab.classList.add('text-gray-600', 'rounded-xl');
        });

        activeTab.classList.remove('text-gray-600', 'rounded-xl');
        activeTab.classList.add('text-[#0455b7]', 'bg-white', 'rounded-lg');

        const selectedRole = activeTab.dataset.role || 'all';

        tableRows.forEach(row => {
            if (row.cells.length > 5) {
                const role = row.cells[5].textContent.trim().toLowerCase();
                if (selectedRole === 'all' || role === selectedRole) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab));
    });

    switchTab(tabs[0]);

    selectAllCheckbox.addEventListener('click', function () {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

        // Handle toggle switch change event
        document.querySelectorAll('input[id^="account-status-"]').forEach(function (toggleSwitch) {
            toggleSwitch.addEventListener('change', function () {
                const userId = this.getAttribute('data-user-id');
                const newStatus = this.checked ? 1 : 0;
    
                // Redirect to the appropriate PHP script
                if (newStatus === 1) {
                    window.location.href = `gerer_les_comptes/enable_user.php?id=${userId}`;
                } else {
                    window.location.href = `gerer_les_comptes/disable_user.php?id=${userId}`;
                }
            });
        });


        // JavaScript code for handling the modal and form submission
        const modifyButtons = document.querySelectorAll('.modify-user-btn');
        const modalOverlay = document.getElementById('modal-overlay');
        const closeModalButton = document.getElementById('close-modal');

        modifyButtons.forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');
                const nom = button.getAttribute('data-nom');
                const prenom = button.getAttribute('data-prenom');
                const email = button.getAttribute('data-email');
                const roleId = button.getAttribute('data-role-id');

                // Populate form fields
                document.getElementById('user_id').value = userId;
                document.getElementById('nom').value = nom;
                document.getElementById('prenom').value = prenom;
                document.getElementById('email').value = email;

                // Role Mapping
                let roleName = (roleId == 1) ? 'Admin' : (roleId == 2) ? 'Technicien' : 'Receveur';
                document.getElementById('account_type').textContent = roleName;
                document.getElementById('role_id').value = roleId;

                // Show modal
                modalOverlay.classList.remove('hidden');
            });
        });

        closeModalButton.addEventListener('click', () => {
            modalOverlay.classList.add('hidden');
        });

        modalOverlay.addEventListener('click', (event) => {
            if (event.target === modalOverlay) {
                modalOverlay.classList.add('hidden');
            }
        });
}

// إعادة تعريف وظيفة البحث كدالة منفصلة لإعادة استخدامها
function performSearch() {
    const searchText = $('#search-input').val().toLowerCase();
    
    if (searchText.trim() === '') {
        // إذا كان حقل البحث فارغًا، نعود إلى الحالة الافتراضية للتبويب النشط
        if (nouveauTab.hasClass('bg-white')) {
            filterNewOrders(true);
        } else {
            filterNewOrders(false);
        }
        $('#no-results-message').remove();
        return;
    }
    
    // البحث في جميع الخلايا
    $('.tr-body').each(function() {
        let found = false;
        $(this).find('td').each(function() {
            if ($(this).text().toLowerCase().includes(searchText)) {
                found = true;
                return false; // الخروج من الحلقة
            }
        });
        
        if (found) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    // عرض رسالة إذا لم يكن هناك نتائج
    if ($('.tr-body:visible').length === 0) {
        if ($('#no-results-message').length === 0) {
            $('table').after('<div id="no-results-message" class="text-center py-4">Aucun résultat trouvé pour "' + searchText + '".</div>');
        } else {
            $('#no-results-message').text('Aucun résultat trouvé pour "' + searchText + '".');
        }
    } else {
        $('#no-results-message').remove();
    }
    
    // إلغاء تحديد "تحديد الكل" عند البحث
    selectAllCheckbox.prop('checked', false);
}

// تطبيق البحث التلقائي عند الكتابة في حقل البحث
$('#search-input').on('input', function() {
    performSearch();
});

// الاحتفاظ بمعالج حدث النقر على زر البحث للتوافق
$('#search-button').on('click', function() {
    performSearch();
});

// الاحتفاظ بمعالج حدث الضغط على Enter (اختياري)
$('#search-input').on('keypress', function(e) {
    if (e.which === 13) {
        e.preventDefault(); // منع إرسال النموذج إذا كان داخل نموذج
        performSearch();
    }
});

// Execute the function when the script is loaded
executeManagUsersJavaScript();
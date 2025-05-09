function executeGererODJavaScript() {
    $(document).ready(function() {
        // Basic variants
        const allTab = $('#allTab');
        const nouveauTab = $('#nouveauTab');
        const selectAllCheckbox = $('#select-all');
        const searchInput = $('#search-input');
        
        function setActiveTab(activeTab, inactiveTab) {
            activeTab.addClass('bg-white text-[#0455b7]').removeClass('text-gray-600');
            inactiveTab.addClass('text-gray-600').removeClass('bg-white text-[#0455b7]');
        }
        
        // Filter the table based on a condition "nouveau"
        function filterNewOrders(showOnlyNew) {
            const rows = $('.tr-body');
            
            if (showOnlyNew) {
                // Get the current date
                const currentDate = new Date();
                // Set a date 1 days in advance (to consider items within the last 1 day as new items)
                const oneWeekAgo = new Date();
                oneWeekAgo.setDate(currentDate.getDate() - 1);
                
                rows.each(function() {
                    const dateString = $(this).find('td:nth-child(7)').text(); // date_depart
                    if (dateString) {
                        const dateParts = dateString.split('-');
                        if (dateParts.length === 3) {
                            const orderDate = new Date(dateParts[0], dateParts[1]-1, dateParts[2]);
                            
                            // If the date is more recent than 1 days, consider it new
                            if (orderDate >= oneWeekAgo) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        } else {
                            $(this).hide();
                        }
                    } else {
                        // If there is no date, we hide the row
                        $(this).hide();
                    }
                });
            } else {
                // Show all rows
                rows.show();
            }
            
            // message if there are no results
            if (showOnlyNew && $('.tr-body:visible').length === 0) {
                if ($('#no-results-message').length === 0) {
                    $('table').after('<div id="no-results-message" class="text-center py-4">Aucun nouvel ordre de mission trouvé.</div>');
                }
            } else {
                $('#no-results-message').remove();
            }
            
            // إلغاء تحديد "تحديد الكل" عند تغيير التبويب
            selectAllCheckbox.prop('checked', false);
            $('input[name="select-order"]').prop('checked', false);
        }
        
        // معالجة النقر على التبويبات
        allTab.on('click', function() {
            setActiveTab(allTab, nouveauTab);
            filterNewOrders(false);
        });
        
        nouveauTab.on('click', function() {
            setActiveTab(nouveauTab, allTab);
            filterNewOrders(true);
        });
        
        // وظيفة البحث الأساسية
        function performSearch() {
            const searchText = searchInput.val().toLowerCase();
            
            if (searchText.trim() === '') {
                // إذا كان حقل البحث فارغًا، نعود إلى الحالة الافتراضية للتبويب النشط
                if (nouveauTab.hasClass('bg-white')) {
                    filterNewOrders(true);
                } else {
                    filterNewOrders(false);
                }
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
        
        // تطبيق البحث التلقائي عند الكتابة في حقل البحث - هذا هو التغيير الأساسي
        searchInput.on('input', function() {
            performSearch();
        });
        
        // الاحتفاظ بمعالج حدث النقر على زر البحث للتوافق
        $('#search-button').on('click', function() {
            performSearch();
        });
        
        // الاحتفاظ بمعالج حدث الضغط على Enter
        searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault(); // منع إرسال النموذج إذا كان داخل نموذج
                performSearch();
            }
        });
        
        // Activation of the drop-down list of actions
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
        
        // Activate the default tab (All) when the page loads
        setActiveTab(allTab, nouveauTab);
        
        // "تحديد الكل" وظيفة زر
        selectAllCheckbox.on('change', function() {
            const visibleCheckboxes = $('input[name="select-order"]').filter(function() {
                return $(this).closest('tr').is(':visible');
            });
            
            visibleCheckboxes.prop('checked', $(this).prop('checked'));
        });
        
        // التحقق من حالة "تحديد الكل" عند تغيير حالة مربعات الاختيار الفردية
        $(document).on('change', 'input[name="select-order"]', function() {
            const visibleCheckboxes = $('input[name="select-order"]').filter(function() {
                return $(this).closest('tr').is(':visible');
            });
            
            selectAllCheckbox.prop('checked', visibleCheckboxes.length === visibleCheckboxes.filter(':checked').length);
        });
    });
}
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
// Script to handle export button click
document.addEventListener('DOMContentLoaded', function () {
    const exportButton = document.getElementById('export-button');
    const exportDropdown = document.getElementById('export-dropdown');

    if (exportButton && exportDropdown) {
        // Toggle dropdown visibility when export button is clicked
        exportButton.addEventListener('click', function (e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('hidden');
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!exportButton.contains(e.target) && !exportDropdown.contains(e.target)) {
                exportDropdown.classList.add('hidden');
            }
        });
    }
});
// Execute the function when the script is loaded
executeGererODJavaScript();
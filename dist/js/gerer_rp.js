

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
                    
$(document).ready(function() {
    
    const allTab = $('#allTab');
    const nouveauTab = $('#nouveauTab');
    const selectAllCheckbox = $('#select-all');
    
    function setActiveTab(activeTab, inactiveTab) {
        activeTab.addClass('bg-white text-[#0455b7]').removeClass('text-gray-600');
        inactiveTab.addClass('text-gray-600').removeClass('bg-white text-[#0455b7]');
    }
    
    function filterNewOrders(showOnlyNew) {
        const rows = $('.tr-body');
        
        if (showOnlyNew) {
            
            
            const currentDate = new Date();
            // Set a date 5 days in advance (to consider items within the last 7 days as new items)
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(currentDate.getDate() - 5);
            
            rows.each(function() {
                const dateString = $(this).find('td:nth-child(3)').text();  
                if (dateString) {
                    //   
                    const dateParts = dateString.split('-');
                    if (dateParts.length === 3) {
                        const orderDate = new Date(dateParts[0], dateParts[1]-1, dateParts[2]);
                        
                        // If the date is more recent than 5 days, consider it new
                        if (orderDate >= oneWeekAgo) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else {
                        
                        $(this).hide();
                    }
                } else {
                    
                    $(this).hide();
                }
            });
        } else {
            // إظهار جميع الصفوف
            rows.show();
        }
        
        if (showOnlyNew && $('.tr-body:visible').length === 0) {
            if ($('#no-results-message').length === 0) {
                $('table').after('<div id="no-results-message" class="text-center py-4">Aucun nouvel ordre de mission trouvé.</div>');
            }
        } else {
            $('#no-results-message').remove();
        }
        
        selectAllCheckbox.prop('checked', false);
        $('input[name="select-order"]').prop('checked', false);
    }
    
    
    allTab.on('click', function() {
        setActiveTab(allTab, nouveauTab);
        filterNewOrders(false);
    });
    
    nouveauTab.on('click', function() {
        setActiveTab(nouveauTab, allTab);
        filterNewOrders(true);
    });
    
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
    // تفعيل القائمة المنسدلة للإجراءات
    $(document).on('click', '[data-dropdown-toggle="dropdownDots"]', function() {
        const dropdown = $(this).next('.z-10');
        if (dropdown.hasClass('hidden')) {
            // إغلاق جميع القوائم المنسدلة المفتوحة أولاً
            $('.z-10').addClass('hidden');
            // ثم فتح القائمة المنسدلة الحالية
            dropdown.removeClass('hidden');
            
            // تحديد موقع القائمة المنسدلة
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
        if (!$(e.target).is('[data-dropdown-toggle="dropdownDots"]') && !$(e.target).parents('[data-dropdown-toggle="dropdownDots"]').length) {
            $('.z-10').addClass('hidden');
        }
    });
    
    setActiveTab(allTab, nouveauTab);
    
    
    selectAllCheckbox.on('change', function() {
        
        const visibleCheckboxes = $('input[name="select-rapport"]').filter(function() {
            return $(this).closest('tr').is(':visible');
        });
        
        visibleCheckboxes.prop('checked', $(this).prop('checked'));
    });
    
    
    $(document).on('change', 'input[name="select-rapport"]', function() {
        
        const visibleCheckboxes = $('input[name="select-rapport"]').filter(function() {
            return $(this).closest('tr').is(':visible');
        });
        
        
        selectAllCheckbox.prop('checked', visibleCheckboxes.length === visibleCheckboxes.filter(':checked').length);
    });
});
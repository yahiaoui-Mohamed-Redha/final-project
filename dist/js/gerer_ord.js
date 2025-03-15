$(document).ready(function() {
    // Basic variants
    const allTab = $('#allTab');
    const nouveauTab = $('#nouveauTab');
    const selectAllCheckbox = $('#select-all');
    
    
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
            // Set a date 1 days in advance (to consider items within the last 7 days as new items
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
    
    // Processing the search in the table
    $('#search-button').on('click', function() {
        const searchText = $('#search-input').val().toLowerCase();
        
        if (searchText.trim() === '') {
            // If the search field is empty, we return to the default state of the active tab
            if (nouveauTab.hasClass('bg-white')) {
                filterNewOrders(true);
            } else {
                filterNewOrders(false);
            }
            return;
        }
        
        // Search all cells
        $('.tr-body').each(function() {
            let found = false;
            $(this).find('td').each(function() {
                if ($(this).text().toLowerCase().includes(searchText)) {
                    found = true;
                    return false; // Getting out of the loop
                }
            });
            
            if (found) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        // Display a message if there are no results
        if ($('.tr-body:visible').length === 0) {
            if ($('#no-results-message').length === 0) {
                $('table').after('<div id="no-results-message" class="text-center py-4">Aucun résultat trouvé pour "' + searchText + '".</div>');
            } else {
                $('#no-results-message').text('Aucun résultat trouvé pour "' + searchText + '".');
            }
        } else {
            $('#no-results-message').remove();
        }
        
        selectAllCheckbox.prop('checked', false);
    });
    
    // Resets the search when clearing text from the search field
    $('#search-input').on('input', function() {
        if ($(this).val() === '') {
            // Resets the display based on the active tab
            if (nouveauTab.hasClass('bg-white')) {
                filterNewOrders(true);
            } else {
                filterNewOrders(false);
            }
        }
    });
    
    // Activating the event of clicking on the search button when pressing ent in the search field
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#search-button').click();
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
    
    
    selectAllCheckbox.on('change', function() {
        
        const visibleCheckboxes = $('input[name="select-order"]').filter(function() {
            return $(this).closest('tr').is(':visible');
        });
        

        visibleCheckboxes.prop('checked', $(this).prop('checked'));
    });
    
    
    $(document).on('change', 'input[name="select-order"]', function() {
        
        const visibleCheckboxes = $('input[name="select-order"]').filter(function() {
            return $(this).closest('tr').is(':visible');
        });
        
        
        selectAllCheckbox.prop('checked', visibleCheckboxes.length === visibleCheckboxes.filter(':checked').length);
    });
});
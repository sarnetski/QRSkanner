/**
 * QR-System Professional Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // === GLOBAL VARIABLES ===
    let selectedCodes = [];
    
    // === INITIALIZATION ===
    initializeAdmin();
    
    function initializeAdmin() {
        initCodeForm();
        initCodesTable();
        initFilters();
        initBulkActions();
        initTooltips();
    }
    
    // === CODE FORM FUNCTIONS ===
    function initCodeForm() {
        const codeInput = $('#code');
        const previewContainer = $('#qr-preview');
        
        // Auto-uppercase dla kodu
        codeInput.on('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            updateQRPreview();
        });
        
        // Auto-preview
        $('#message, #type').on('change', updateQRPreview);
        
        // Number input controls
        $('.qr-number-controls button').on('click', function(e) {
            e.preventDefault();
            const input = $(this).closest('.qr-number-input').find('input');
            const change = $(this).text() === '+' ? 1 : -1;
            adjustNumber(input.attr('name'), change);
        });
        
        // Formularz validation
        $('.qr-form').on('submit', function(e) {
            if (!validateCodeForm()) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    window.generateRandomCode = function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#code').val(result).trigger('input');
    };
    
    window.adjustNumber = function(fieldName, change) {
        const input = $(`input[name="${fieldName}"]`);
        const currentValue = parseInt(input.val()) || 1;
        const min = parseInt(input.attr('min')) || 1;
        const max = parseInt(input.attr('max')) || 10000;
        
        const newValue = Math.max(min, Math.min(max, currentValue + change));
        input.val(newValue);
    };
    
    function updateQRPreview() {
        const code = $('#code').val().trim();
        const preview = $('#qr-preview');
        
        if (!code) {
            preview.html(`
                <span class="dashicons dashicons-qrcode"></span>
                <p>Wpisz kod aby zobaczyć podgląd</p>
            `).removeClass('qr-preview-active').addClass('qr-preview-placeholder');
            return;
        }
        
        // Generuj QR code używając API
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(code)}`;
        
        preview.html(`
            <img src="${qrApiUrl}" alt="QR Code: ${code}" class="qr-preview-image">
            <div class="qr-preview-code">${code}</div>
        `).removeClass('qr-preview-placeholder').addClass('qr-preview-active');
    }
    
    window.refreshPreview = function() {
        updateQRPreview();
        showToast('Podgląd odświeżony', 'success');
    };
    
    window.downloadQRCode = function() {
        const code = $('#code').val().trim();
        if (!code) {
            showToast('Wpisz kod QR', 'error');
            return;
        }
        
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(code)}`;
        const link = document.createElement('a');
        link.href = qrApiUrl;
        link.download = `qr-code-${code}.png`;
        link.click();
        
        showToast('Pobieranie rozpoczęte', 'success');
    };
    
    function validateCodeForm() {
        const code = $('#code').val().trim();
        
        if (!code) {
            showToast('Kod QR jest wymagany', 'error');
            $('#code').focus();
            return false;
        }
        
        if (!/^[A-Z0-9]+$/.test(code)) {
            showToast('Kod może zawierać tylko litery i cyfry', 'error');
            $('#code').focus();
            return false;
        }
        
        const maxUses = parseInt($('#max_uses').val());
        if (!maxUses || maxUses < 1) {
            showToast('Maksymalna liczba użyć musi być większa od 0', 'error');
            $('#max_uses').focus();
            return false;
        }
        
        return true;
    }
    
    // === TABLE FUNCTIONS ===
    function initCodesTable() {
        // Select all checkbox
        $('#qr-select-all').on('change', function() {
            const checked = this.checked;
            $('.qr-code-checkbox').prop('checked', checked);
            updateSelectedCodes();
        });
        
        // Individual checkboxes
        $('.qr-code-checkbox').on('change', function() {
            updateSelectedCodes();
            
            // Update select all state
            const total = $('.qr-code-checkbox').length;
            const selected = $('.qr-code-checkbox:checked').length;
            
            $('#qr-select-all').prop('checked', selected === total);
            $('#qr-select-all').prop('indeterminate', selected > 0 && selected < total);
        });
        
        // Row click to select
        $('.qr-code-row').on('click', function(e) {
            if ($(e.target).closest('.qr-actions-menu, .qr-code-checkbox').length) return;
            
            const checkbox = $(this).find('.qr-code-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        });
    }
    
    function updateSelectedCodes() {
        selectedCodes = [];
        $('.qr-code-checkbox:checked').each(function() {
            selectedCodes.push(parseInt($(this).val()));
        });
        
        const bulkActions = $('#qr-bulk-actions');
        const selectedCount = $('#qr-selected-count');
        
        if (selectedCodes.length > 0) {
            bulkActions.show();
            selectedCount.text(selectedCodes.length);
        } else {
            bulkActions.hide();
        }
    }
    
    // === FILTERS ===
    function initFilters() {
        const searchInput = $('#qr-search-codes');
        const filterGroup = $('#qr-filter-group');
        const filterStatus = $('#qr-filter-status');
        const filterType = $('#qr-filter-type');
        
        // Search functionality
        let searchTimeout;
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterTable();
            }, 300);
        });
        
        // Filter dropdowns
        filterGroup.add(filterStatus).add(filterType).on('change', filterTable);
        
        // Search button
        $('.qr-search-btn').on('click', filterTable);
    }
    
    function filterTable() {
        const searchTerm = $('#qr-search-codes').val().toLowerCase();
        const filterGroup = $('#qr-filter-group').val();
        const filterStatus = $('#qr-filter-status').val();
        const filterType = $('#qr-filter-type').val();
        
        let visibleCount = 0;
        
        $('.qr-code-row').each(function() {
            const $row = $(this);
            const code = $row.find('.qr-code-value').text().toLowerCase();
            const message = $row.find('.qr-code-message').text().toLowerCase();
            const group = $row.data('group');
            const status = $row.data('status');
            const type = $row.data('type');
            
            let visible = true;
            
            // Search filter
            if (searchTerm && !code.includes(searchTerm) && !message.includes(searchTerm)) {
                visible = false;
            }
            
            // Group filter
            if (filterGroup && group != filterGroup) {
                visible = false;
            }
            
            // Status filter
            if (filterStatus && status !== filterStatus) {
                visible = false;
            }
            
            // Type filter
            if (filterType && type !== filterType) {
                visible = false;
            }
            
            if (visible) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        // Show/hide empty state
        const emptyRow = $('.qr-empty-row');
        if (visibleCount === 0 && !emptyRow.length) {
            $('.qr-codes-tbody').append(`
                <tr class="qr-empty-row qr-filtered-empty">
                    <td colspan="9" class="qr-empty-cell">
                        <div class="qr-empty-state">
                            <span class="dashicons dashicons-search"></span>
                            <h3>Brak wyników</h3>
                            <p>Nie znaleziono kodów spełniających kryteria wyszukiwania</p>
                            <button type="button" class="button" onclick="clearFilters()">Wyczyść filtry</button>
                        </div>
                    </td>
                </tr>
            `);
        } else if (visibleCount > 0) {
            $('.qr-filtered-empty').remove();
        }
    }
    
    window.clearFilters = function() {
        $('#qr-search-codes').val('');
        $('#qr-filter-group, #qr-filter-status, #qr-filter-type').val('');
        $('.qr-filtered-empty').remove();
        $('.qr-code-row').show();
    };
    
    // === BULK ACTIONS ===
    function initBulkActions() {
        // Bulk action handlers are defined as global functions
    }
    
    window.bulkAction = function(action) {
        if (selectedCodes.length === 0) {
            showToast('Nie wybrano żadnych kodów', 'error');
            return;
        }
        
        let confirmMessage = '';
        switch (action) {
            case 'activate':
                confirmMessage = `Czy na pewno chcesz aktywować ${selectedCodes.length} kodów?`;
                break;
            case 'deactivate':
                confirmMessage = `Czy na pewno chcesz dezaktywować ${selectedCodes.length} kodów?`;
                break;
            case 'delete':
                confirmMessage = `Czy na pewno chcesz usunąć ${selectedCodes.length} kodów? Ta akcja jest nieodwracalna.`;
                break;
        }
        
        if (!confirm(confirmMessage)) return;
        
        // Wykonaj akcję masową
        $.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_bulk_action',
                nonce: qr_ajax.nonce,
                bulk_action: action,
                codes: selectedCodes
            },
            beforeSend: function() {
                showToast('Wykonuję akcję...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.data || 'Błąd podczas wykonywania akcji', 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    };
    
    // === ACTION MENU ===
    window.toggleActionMenu = function(button) {
        const menu = $(button).siblings('.qr-actions-dropdown');
        const isVisible = menu.is(':visible');
        
        // Zamknij wszystkie inne menu
        $('.qr-actions-dropdown').hide();
        
        // Przełącz aktualne menu
        if (!isVisible) {
            menu.show();
            
            // Zamknij menu po kliknięciu poza nim
            $(document).one('click', function(e) {
                if (!$(e.target).closest('.qr-actions-menu').length) {
                    menu.hide();
                }
            });
        }
    };
    
    // === CODE ACTIONS ===
    window.deleteCode = function(codeId) {
        if (!confirm('Czy na pewno chcesz usunąć ten kod QR? Ta akcja jest nieodwracalna.')) {
            return;
        }
        
        $.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_delete_code',
                nonce: qr_ajax.nonce,
                id: codeId
            },
            beforeSend: function() {
                showToast('Usuwam kod...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showToast('Kod został usunięty', 'success');
                    $(`tr[data-id="${codeId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        updateSelectedCodes();
                    });
                } else {
                    showToast(response.data || 'Błąd podczas usuwania kodu', 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    };
    
    window.duplicateCode = function(codeId) {
        $.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_duplicate_code',
                nonce: qr_ajax.nonce,
                id: codeId
            },
            beforeSend: function() {
                showToast('Duplikuję kod...', 'info');
            },
            success: function(response) {
                if (response.success) {
                    showToast('Kod został zduplikowany', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.data || 'Błąd podczas duplikowania kodu', 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    };
    
    window.generateQRImage = function(code) {
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(code)}`;
        const link = document.createElement('a');
        link.href = qrApiUrl;
        link.download = `qr-code-${code}.png`;
        link.click();
        
        showToast('Pobieranie kodu QR rozpoczęte', 'success');
    };
    
    // === EXPORT FUNCTIONS ===
    window.qrExportCodes = function() {
        const visibleRows = $('.qr-code-row:visible');
        
        if (visibleRows.length === 0) {
            showToast('Brak kodów do eksportu', 'error');
            return;
        }
        
        const data = [];
        
        // Header
        data.push([
            'Kod QR',
            'Grupa', 
            'Typ',
            'Status',
            'Wiadomość',
            'Użycia aktualne',
            'Użycia maksymalne',
            'Data ważności',
            'Data utworzenia'
        ]);
        
        // Dane
        visibleRows.each(function() {
            const $row = $(this);
            data.push([
                $row.find('.qr-code-value').text().trim(),
                $row.find('.qr-group-badge').text().trim() || 'Bez grupy',
                $row.find('.qr-type-badge').text().trim(),
                $row.find('.qr-status-badge').text().trim(),
                $row.find('.qr-code-message').text().trim(),
                $row.find('.qr-usage-count').text().split('/')[0].trim(),
                $row.find('.qr-usage-count').text().split('/')[1].trim(),
                $row.find('.qr-expiry-date').text().trim() || 'Bezterminowy',
                $row.find('.qr-created-date').text().trim()
            ]);
        });
        
        downloadCSV(data, `qr-kody-${getCurrentDate()}.csv`);
        showToast(`Eksportowano ${visibleRows.length} kodów`, 'success');
    };
    
    // === TOOLTIPS ===
    function initTooltips() {
        // Dodaj tooltips dla przycisków akcji
        $('.qr-action-btn').each(function() {
            const title = $(this).attr('title');
            if (title) {
                $(this).tooltip({
                    position: { my: "center bottom-20", at: "center top" }
                });
            }
        });
    }
    
    // === UTILITY FUNCTIONS ===
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="qr-toast qr-toast-${type}">
                <div class="qr-toast-content">
                    <span class="dashicons dashicons-${getToastIcon(type)}"></span>
                    <span class="qr-toast-message">${message}</span>
                    <button class="qr-toast-close">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        // Show toast
        setTimeout(() => toast.addClass('qr-toast-show'), 100);
        
        // Auto remove
        setTimeout(() => {
            toast.removeClass('qr-toast-show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
        
        // Manual close
        toast.find('.qr-toast-close').on('click', function() {
            toast.removeClass('qr-toast-show');
            setTimeout(() => toast.remove(), 300);
        });
    }
    
    function getToastIcon(type) {
        const icons = {
            success: 'yes-alt',
            error: 'warning',
            info: 'info',
            warning: 'flag'
        };
        return icons[type] || 'info';
    }
    
    function getCurrentDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    function downloadCSV(data, filename) {
        const csv = data.map(row => 
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`)
               .join(',')
        ).join('\n');
        
        const BOM = '\uFEFF'; // UTF-8 BOM for Excel compatibility
        const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // === KEYBOARD SHORTCUTS ===
    $(document).on('keydown', function(e) {
        // Ctrl+N = Nowy kod
        if (e.ctrlKey && e.key === 'n' && window.location.href.includes('qr-system-codes')) {
            e.preventDefault();
            window.location.href = qr_ajax.url.replace('admin-ajax.php', 'admin.php?page=qr-system-codes&action=add');
        }
        
        // Ctrl+F = Fokus na wyszukiwanie
        if (e.ctrlKey && e.key === 'f' && $('#qr-search-codes').length) {
            e.preventDefault();
            $('#qr-search-codes').focus();
        }
        
        // Escape = Zamknij menu akcji
        if (e.key === 'Escape') {
            $('.qr-actions-dropdown').hide();
        }
    });
});

// === GLOBAL FUNCTIONS (for external calls) ===

// Initialize code form (called from template)
function initCodeForm() {
    // This is called from the template, actual initialization is in jQuery ready
}

// Initialize codes table (called from template) 
function initCodesTable() {
    // This is called from the template, actual initialization is in jQuery ready
}
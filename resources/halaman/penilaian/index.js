import { ApiService } from "../../js/utils/ApiService";

const statusSelects = document.querySelectorAll('.status-select');

statusSelects.forEach(select => {
    updateSelectAppearance(select);
    
    select.addEventListener('change', function() {
        updateSelectAppearance(this);
    });
});

function updateSelectAppearance(select) {
    const selectedOption = select.options[select.selectedIndex];
    const color = selectedOption.getAttribute('data-color');
    
    // Remove existing color classes
    select.classList.remove('border-success', 'border-danger');
    
    // Add new color class
    if (color) {
        select.classList.add(`border-${color}`);
    }
}

// Add shadow to sticky header when scrolled
const tableContainer = document.querySelector('.table-container');
const stickyHeader = document.querySelector('.sticky-header');

if (tableContainer && stickyHeader) {
    tableContainer.addEventListener('scroll', function() {
        if (this.scrollTop > 0) {
            stickyHeader.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        } else {
            stickyHeader.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
        }
    });
}

// AJAX Update dengan Fetch API
const updateButtons = document.querySelectorAll('.update-btn');

updateButtons.forEach(button => {
    button.addEventListener('click', function() {
        const ruleId = this.getAttribute('data-rule-id');
        updateRuleStatus(ruleId);
    });
});

// Fungsi untuk update status dengan Fetch API
async function updateRuleStatus(ruleId) {
    const selectElement = document.querySelector(`.status-select[data-rule-id="${ruleId}"]`);
    const buttonElement = document.querySelector(`.update-btn[data-rule-id="${ruleId}"]`);
    const statusOutput = selectElement.value;

    // Validasi
    if (!statusOutput) {
        ApiService.showAlert('Pilih status terlebih dahulu!', 'danger');
        return;
    }

    // Set loading state
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
    buttonElement.classList.add('loading');
    buttonElement.disabled = true;

    try {
        const response = await ApiService.call(`/penilaian/${ruleId}`, 'PUT', {status_output: statusOutput})

        if (response) {
            // Success
            ApiService.showAlert(response.message || 'Status berhasil diperbarui!', 'success');
            buttonElement.innerHTML = '<i class="fas fa-check me-1"></i> Berhasil';
            buttonElement.classList.add('success');
            
            // Reset button setelah 2 detik
            setTimeout(() => {
                buttonElement.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
                buttonElement.classList.remove('loading', 'success');
                buttonElement.disabled = false;
            }, 2000);
        } else {
            // Error dari server
            throw new Error(response.message || 'Terjadi kesalahan saat memperbarui status');
        }

    } catch (error) {
        ApiService.showAlert(error.message || 'Terjadi kesalahan saat memperbarui status', 'danger');
        
        // Reset button state
        buttonElement.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
        buttonElement.classList.remove('loading', 'success');
        buttonElement.disabled = false;
    }
}

statusSelects.forEach(select => {
    select.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const ruleId = this.getAttribute('data-rule-id');
            updateRuleStatus(ruleId);
        }
    });
});
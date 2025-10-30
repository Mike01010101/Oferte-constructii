import './bootstrap';
import Swup from 'swup';

// --- GESTIONARE EVENIMENTE GLOBALE ---
document.addEventListener('click', function(event) {
    const themeSwitcher = event.target.closest('#theme-switcher');
    if (themeSwitcher) {
        const htmlElement = document.documentElement;
        const currentTheme = htmlElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        htmlElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }
});


// --- FUNCȚII HELPER (care trebuie re-inițializate la fiecare pagină) ---

function handleSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if (sidebarToggle && !sidebarToggle.listenerAttached) {
        sidebarToggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        sidebarToggle.listenerAttached = true;
    }

    if (sidebarOverlay && !sidebarOverlay.listenerAttached) {
        sidebarOverlay.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
        sidebarOverlay.listenerAttached = true;
    }
}

function handleClientSearch() {
    const searchInput = document.getElementById('search-input');
    const tableContainer = document.getElementById('clients-table-container');
    if (!searchInput || !tableContainer) return;

    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value;
            const url = `/clienti?search=${encodeURIComponent(searchTerm)}`;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => { tableContainer.innerHTML = html; })
                .catch(error => console.error('A apărut o eroare la căutare:', error));
        }, 300);
    });
}

function handleDeleteModal(modalId, formIdAttribute) {
    const deleteModal = document.getElementById(modalId);
    if (!deleteModal) return;

    const confirmDeleteBtn = deleteModal.querySelector('#confirmDeleteBtn');
    let formToSubmitId = null;

    // Folosim o variabilă pentru a ne asigura că listener-ul este atașat o singură dată
    if (!deleteModal.listenerAttached) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            formToSubmitId = button.getAttribute(formIdAttribute);
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                if (formToSubmitId) {
                    const form = document.getElementById(formToSubmitId);
                    if (form) form.submit();
                }
            });
        }
        deleteModal.listenerAttached = true;
    }
}


function handleOfferSearch() {
    const searchInput = document.getElementById('search-input');
    const tableContainer = document.getElementById('offers-table-container');
    if (!searchInput || !tableContainer) return;

    let debounceTimer;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value;
            const url = `/oferte?search=${encodeURIComponent(searchTerm)}`;
            fetch(url, { headers: { 'X-Requested-with': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => { tableContainer.innerHTML = html; })
                .catch(error => console.error('A apărut o eroare:', error));
        }, 300);
    });
}

function handleOfferSettingsToggles() {
    const showSummary = document.getElementById('show_summary_block');
    const includeInPrices = document.getElementById('include_summary_in_prices');
    if (!showSummary || !includeInPrices) return;

    function toggleOptions() {
        includeInPrices.disabled = false; // Asigurăm că nu rămâne blocat
        showSummary.disabled = false;

        if (includeInPrices.checked) {
            showSummary.checked = false;
            showSummary.disabled = true;
        }
    }
    
    // Asigurăm că listener-ul nu se adaugă de mai multe ori
    if (!includeInPrices.listenerAttached) {
        includeInPrices.addEventListener('change', toggleOptions);
        includeInPrices.listenerAttached = true;
    }
    toggleOptions();
}

function handleOfferForm() {
    // ... tot codul pentru handleOfferForm rămâne neschimbat aici ...
    const tbody = document.getElementById('offer-items-tbody');
    if (!tbody) return;

    const settings = {
        showMaterial: tbody.dataset.showMaterial === 'true',
        showLabor: tbody.dataset.showLabor === 'true',
        showEquipment: tbody.dataset.showEquipment === 'true',
        showUnitPrice: tbody.dataset.showUnitPrice === 'true',
    };

    const addBtn = document.getElementById('add-item-btn');
    const grandTotalElem = document.getElementById('grand-total');
    const priceModeToggle = document.getElementById('price-mode-toggle');
    let itemIndex = tbody.children.length;

    const isTotalMode = () => priceModeToggle.checked;

    const addRow = () => {
        const row = document.createElement('tr');
        row.classList.add('offer-item-row');
        let priceCells = '';
        if (settings.showMaterial) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible material-price-visible" step="0.01" value="0.00"></td>`;
        if (settings.showLabor) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible labor-price-visible" step="0.01" value="0.00"></td>`;
        if (settings.showEquipment) priceCells += `<td><input type="number" class="form-control form-control-sm price-input-visible equipment-price-visible" step="0.01" value="0.00"></td>`;
        if (settings.showUnitPrice) priceCells += `<td class="text-end align-middle unit-price-total">0.00</td>`;

        row.innerHTML = `
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm description-input" required>
                <input type="hidden" name="items[${itemIndex}][material_price]" class="price-input-hidden material-price-hidden" value="0.00">
                <input type="hidden" name="items[${itemIndex}][labor_price]" class="price-input-hidden labor-price-hidden" value="0.00">
                <input type="hidden" name="items[${itemIndex}][equipment_price]" class="price-input-hidden equipment-price-hidden" value="0.00">
            </td>
            <td><input type="text" name="items[${itemIndex}][unit_measure]" class="form-control form-control-sm" value="buc" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity" step="0.01" value="1" required></td>
            ${priceCells}
            <td class="text-end align-middle line-total">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class="fa-solid fa-trash-can"></i></button></td>
        `;
        tbody.appendChild(row);
        updateEventListenersForRow(row);
        row.querySelector('.description-input').focus();
        itemIndex++;
    };

    const updateCalculations = () => { 
        let grandTotal = 0;
        document.querySelectorAll('.offer-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            let materialValue = parseFloat(row.querySelector('.material-price-visible')?.value) || 0;
            let laborValue = parseFloat(row.querySelector('.labor-price-visible')?.value) || 0;
            let equipmentValue = parseFloat(row.querySelector('.equipment-price-visible')?.value) || 0;
            let unitMaterial, unitLabor, unitEquipment;

            if (isTotalMode()) {
                unitMaterial = (qty > 0) ? materialValue / qty : 0;
                unitLabor = (qty > 0) ? laborValue / qty : 0;
                unitEquipment = (qty > 0) ? equipmentValue / qty : 0;
            } else {
                unitMaterial = materialValue;
                unitLabor = laborValue;
                unitEquipment = equipmentValue;
            }

            if(row.querySelector('.material-price-hidden')) row.querySelector('.material-price-hidden').value = unitMaterial.toFixed(2);
            if(row.querySelector('.labor-price-hidden')) row.querySelector('.labor-price-hidden').value = unitLabor.toFixed(2);
            if(row.querySelector('.equipment-price-hidden')) row.querySelector('.equipment-price-hidden').value = unitEquipment.toFixed(2);
            
            const unitPriceTotal = unitMaterial + unitLabor + unitEquipment;
            const lineTotal = qty * unitPriceTotal;

            if (settings.showUnitPrice && row.querySelector('.unit-price-total')) row.querySelector('.unit-price-total').textContent = unitPriceTotal.toFixed(2);
            if (row.querySelector('.line-total')) row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            grandTotal += lineTotal;
        });
        if (grandTotalElem) grandTotalElem.textContent = grandTotal.toFixed(2) + ' RON';
    };

    const togglePriceMode = () => { 
        const newLabel = isTotalMode() ? '(total)' : '(unitar)';
        document.querySelectorAll('.price-mode-label').forEach(label => label.textContent = newLabel);
        document.querySelectorAll('.offer-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitMaterial = parseFloat(row.querySelector('.material-price-hidden').value) || 0;
            const unitLabor = parseFloat(row.querySelector('.labor-price-hidden').value) || 0;
            const unitEquipment = parseFloat(row.querySelector('.equipment-price-hidden').value) || 0;
            const materialInput = row.querySelector('.material-price-visible');
            const laborInput = row.querySelector('.labor-price-visible');
            const equipmentInput = row.querySelector('.equipment-price-visible');

            if (isTotalMode()) {
                if (materialInput) materialInput.value = (unitMaterial * qty).toFixed(2);
                if (laborInput) laborInput.value = (unitLabor * qty).toFixed(2);
                if (equipmentInput) equipmentInput.value = (unitEquipment * qty).toFixed(2);
            } else {
                if (materialInput) materialInput.value = unitMaterial.toFixed(2);
                if (laborInput) laborInput.value = unitLabor.toFixed(2);
                if (equipmentInput) equipmentInput.value = unitEquipment.toFixed(2);
            }
        });
        updateCalculations();
    };
    
    const updateEventListenersForRow = (row) => {
        row.querySelector('.remove-item-btn').onclick = () => { row.remove(); updateCalculations(); };
        row.querySelectorAll('.quantity, .price-input-visible').forEach(input => { input.oninput = updateCalculations; });
    };

    if (priceModeToggle && !priceModeToggle.listenerAttached) {
        priceModeToggle.addEventListener('change', togglePriceMode);
        priceModeToggle.listenerAttached = true;
    } 
    if (addBtn && !addBtn.listenerAttached) {
        addBtn.addEventListener('click', addRow);
        addBtn.listenerAttached = true;
    }
    
    document.querySelectorAll('.offer-item-row').forEach(row => updateEventListenersForRow(row));
    if (tbody.children.length === 0 && (window.location.href.includes('/oferte/create') || document.querySelector("h1:contains('Creează o ofertă nouă')"))) {
        addRow();
    } else {
        updateCalculations();
    }
}


// NOU: Funcția pentru creatorul de șabloane
function handleTemplateCreator() {
    const previewContainer = document.getElementById('preview-container');
    if (!previewContainer) return;

    const controls = {
        layout: document.getElementById('layout'),
        font: document.getElementById('font_family'),
        color: document.getElementById('accent_color'),
        tableStyle: document.getElementById('table_style'),
        footer: document.getElementById('footer_text'),
    };

    const preview = {
        container: previewContainer,
        header: document.getElementById('preview-header'),
        logo: document.getElementById('preview-logo'),
        titleSection: document.getElementById('preview-title-section'),
        title: document.getElementById('preview-title'),
        table: document.getElementById('preview-table'),
        tableHead: document.getElementById('preview-table-head'),
        footer: document.getElementById('preview-footer'),
    };

    function updatePreview() {
        // Font & Color
        preview.container.style.fontFamily = `'${controls.font.value}', sans-serif`;
        preview.title.style.color = controls.color.value;
        preview.tableHead.style.backgroundColor = controls.color.value;
        preview.tableHead.style.color = 'white';

        // Footer
        preview.footer.textContent = controls.footer.value || 'Termenii și condițiile vor apărea aici.';

        // Table Style
        preview.table.classList.remove('table-bordered', 'table-striped');
        if (controls.tableStyle.value === 'grid') {
            preview.table.classList.add('table-bordered');
        } else {
            preview.table.classList.add('table-striped');
        }

        const layoutStyle = controls.layout.value;

        // Resetare stiluri
        preview.header.className = '';
        preview.logo.style.margin = '';
        preview.titleSection.style.textAlign = 'end';
        preview.header.style.backgroundColor = 'transparent';
        preview.header.style.color = 'inherit';
        preview.logo.classList.add('bg-light', 'border', 'p-3');
        preview.logo.textContent = "Logo Firmă";
        preview.title.style.fontSize = '1.5rem';

        if (layoutStyle === 'classic') {
            preview.header.className = 'd-flex justify-content-between align-items-start mb-4';
        } else if (layoutStyle === 'modern') {
            preview.header.className = 'text-center mb-4';
            preview.logo.style.margin = '0 auto 1rem auto';
            preview.titleSection.style.textAlign = 'center';
            preview.titleSection.style.width = '100%';
        } else if (layoutStyle === 'compact') {
            preview.header.className = 'd-flex justify-content-between align-items-center mb-4 p-3 rounded';
            preview.header.style.backgroundColor = controls.color.value;
            preview.header.style.color = 'white';
            preview.title.style.color = 'white';
            preview.title.style.fontSize = '1.2rem';
            preview.logo.classList.remove('bg-light', 'border', 'p-3');
            preview.logo.textContent = "LOGO";
        }
    }

    Object.values(controls).forEach(control => {
        if (!control.listenerAttached) {
            control.addEventListener('input', updatePreview);
            control.listenerAttached = true;
        }
    });

    updatePreview();
}

// --- FUNCȚIA PRINCIPALĂ DE INIȚIALIZARE ---
function initPage() {
    handleSidebar();
    handleClientSearch();
    handleOfferSearch();
    handleOfferSettingsToggles();
    handleOfferForm();
    handleTemplateCreator(); // Adăugăm noua funcție aici

    // Inițializăm toate modalele
    handleDeleteModal('deleteClientModal', 'data-form-id');
    handleDeleteModal('deleteOfferModal', 'data-form-id');
    handleDeleteModal('deleteUserModal', 'data-form-id'); // Adăugăm și modalul pentru utilizatori
}

// Pornim scripturile la încărcarea inițială a paginii
document.addEventListener('DOMContentLoaded', initPage);
const swup = new Swup({ containers: ['#swup', '#sidebar-nav'] });
swup.hooks.on('visit:end', initPage);
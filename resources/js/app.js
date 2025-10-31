import Chart from 'chart.js/auto';
window.Chart = Chart; 
import './bootstrap';
import Swup from 'swup';
import { Toast, Modal } from 'bootstrap';

let activeCharts = [];
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

function handleDeleteModal(modalId, confirmBtnId, formIdAttribute) {
    const deleteModal = document.getElementById(modalId);
    // Verificăm dacă modal-ul există pe pagină. Dacă nu, ne oprim.
    if (!deleteModal) {
        return;
    }

    const confirmDeleteBtn = document.getElementById(confirmBtnId);
    // Verificăm dacă și butonul de confirmare există.
    if (!confirmDeleteBtn) {
        return;
    }

    let formToSubmitId = null;

    if (!deleteModal.listenerAttached) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            formToSubmitId = button.getAttribute(formIdAttribute);
        });
        deleteModal.listenerAttached = true;
    }

    if (confirmDeleteBtn && !confirmDeleteBtn.listenerAttached) {
        confirmDeleteBtn.addEventListener('click', () => {
            if (formToSubmitId) {
                const form = document.getElementById(formToSubmitId);
                if (form) form.submit();
            }
        });
        confirmDeleteBtn.listenerAttached = true;
    }
}

function handleQuickAssignUpdate() {
    const container = document.querySelector('#offers-table-container');
    if (!container) return;

    // Folosim un nume de proprietate diferit pentru a evita conflictele
    if (!container.listenerAttachedAssign) {
        container.addEventListener('click', function(event) {
            const target = event.target;
            
            if (target.classList.contains('assign-user-btn')) {
                event.preventDefault();

                const offerId = target.dataset.offerId;
                const userId = target.dataset.userId;
                const url = target.dataset.url;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const assignCell = document.getElementById(`assign-cell-${offerId}`);
                        if (assignCell) {
                            const dropdownToggle = assignCell.querySelector('.dropdown-toggle');
                            if(dropdownToggle) {
                                dropdownToggle.textContent = data.assigned_user_name;
                            }
                        }
                        showToast(data.success, 'success');
                    } else {
                        showToast(data.error || 'A apărut o eroare.', 'error');
                    }
                })
                .catch(error => { 
                    console.error('Eroare:', error);
                    showToast('Eroare de rețea.', 'error');
                });
            }
        });
        container.listenerAttachedAssign = true;
    }
}
// Funcție helper globală pentru a afișa notificări
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;

    const icon = type === 'success' 
        ? '<i class="fa-solid fa-check-circle me-2"></i>' 
        : '<i class="fa-solid fa-exclamation-triangle me-2"></i>';
    
    const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';

    const toastHTML = `
        <div class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${icon}${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const newToastEl = toastContainer.lastElementChild;

    if (newToastEl) {
        const toast = new Toast(newToastEl, { delay: 3000 });
        newToastEl.addEventListener('hidden.bs.toast', () => newToastEl.remove());
        toast.show();
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
    const showSummaryBlock = document.getElementById('show_summary_block');
    const includeInPrices = document.getElementById('include_summary_in_prices');

    // Dacă elementele nu există pe pagină, ieșim
    if (!showSummaryBlock || !includeInPrices) {
        return;
    }

    // Funcția care se va ocupa de logica de comutare
    function handleToggle(event) {
        // Identificăm care switch a fost acționat
        const toggledSwitch = event.target;

        if (toggledSwitch === showSummaryBlock && showSummaryBlock.checked) {
            // Dacă am bifat "Afișează blocul", debifăm "Include în preț"
            includeInPrices.checked = false;
        } else if (toggledSwitch === includeInPrices && includeInPrices.checked) {
            // Dacă am bifat "Include în preț", debifăm "Afișează blocul"
            showSummaryBlock.checked = false;
        }
    }

    // Atașăm listener-ul de 'change' la ambele switch-uri, dacă nu a fost deja atașat
    if (!showSummaryBlock.listenerAttached) {
        showSummaryBlock.addEventListener('change', handleToggle);
        showSummaryBlock.listenerAttached = true;
    }
    if (!includeInPrices.listenerAttached) {
        includeInPrices.addEventListener('change', handleToggle);
        includeInPrices.listenerAttached = true;
    }
}

function handleOfferForm() {
    const tbody = document.getElementById('offer-items-tbody');
    if (!tbody) return;

    // NOU: Citim setările direct din atributele data-* ale tbody
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

        // Construim TOATE celulele de preț vizibile într-o singură variabilă
        let priceCellsHTML = '';
        if (settings.showMaterial) {
            priceCellsHTML += `<td><input type="number" class="form-control form-control-sm price-input-visible material-price-visible" step="0.0001" value="0.00"></td>`;
        }
        if (settings.showLabor) {
            priceCellsHTML += `<td><input type="number" class="form-control form-control-sm price-input-visible labor-price-visible" step="0.0001" value="0.00"></td>`;
        }
        if (settings.showEquipment) {
            priceCellsHTML += `<td><input type="number" class="form-control form-control-sm price-input-visible equipment-price-visible" step="0.0001" value="0.00"></td>`;
        }
        if (settings.showUnitPrice) {
            priceCellsHTML += `<td class="text-end align-middle unit-price-total">0.00</td>`;
        }

        // Construim input-urile ascunse separat
        const hiddenInputsHTML = `
            <input type="hidden" name="items[${itemIndex}][material_price]" class="price-input-hidden material-price-hidden" value="0.00">
            <input type="hidden" name="items[${itemIndex}][labor_price]" class="price-input-hidden labor-price-hidden" value="0.00">
            <input type="hidden" name="items[${itemIndex}][equipment_price]" class="price-input-hidden equipment-price-hidden" value="0.00">
        `;
        
        // Combinăm totul în structura finală a rândului, plasând corect variabilele
        row.innerHTML = `
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm description-input" required>
                ${hiddenInputsHTML}
            </td>
            <td><input type="text" name="items[${itemIndex}][unit_measure]" class="form-control form-control-sm" value="buc" required></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity" step="0.01" value="1" required></td>
            
            ${priceCellsHTML}

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

            if(row.querySelector('.material-price-hidden')) row.querySelector('.material-price-hidden').value = unitMaterial.toFixed(4);
            if(row.querySelector('.labor-price-hidden')) row.querySelector('.labor-price-hidden').value = unitLabor.toFixed(4);
            if(row.querySelector('.equipment-price-hidden')) row.querySelector('.equipment-price-hidden').value = unitEquipment.toFixed(4);
            
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
                if (materialInput) materialInput.value = unitMaterial.toFixed(4);
                if (laborInput) laborInput.value = unitLabor.toFixed(4);
                if (equipmentInput) equipmentInput.value = unitEquipment.toFixed(4);
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
    
    if (tbody.children.length > 0) {
        if (priceModeToggle) {
            priceModeToggle.checked = false; // Asigurăm că începem mereu în modul unitar
        }
        itemIndex = tbody.children.length; // Setăm indexul corect pentru rândurile noi
        document.querySelectorAll('.offer-item-row').forEach(row => {
            updateEventListenersForRow(row);
        });
        // Rulăm calculul inițial pentru a afișa totalurile corecte la încărcare
        updateCalculations();
    } 
    // Dacă tabelul este gol (doar cazul Creare Ofertă Nouă)
    else if (document.querySelector("h1").textContent.includes('Creează o ofertă nouă')) {
        addRow();
    }
}


function handleTemplateCreator() {
    const previewContainer = document.getElementById('preview-page');
    if (!previewContainer) return;

    const controls = {
        layout: document.getElementById('layout'),
        title: document.getElementById('document_title'),
        font: document.getElementById('font_family'),
        color: document.getElementById('accent_color'),
        tableStyle: document.getElementById('table_style'),
        footer: document.getElementById('footer_text'),
        stamp: document.getElementById('stamp'),
        stampSize: document.getElementById('stamp_size'),
        introText: document.getElementById('intro_text'),
    };
    const stampSizeValue = document.getElementById('stamp_size_value');

    const preview = {
        page: previewContainer,
        headerContainer: document.getElementById('preview-header-container'),
        table: document.getElementById('preview-table'),
        tableHead: document.getElementById('preview-table-head'),
        stripedRow: document.getElementById('preview-striped-row'),
        footer: document.getElementById('preview-footer'),
        stamp: document.getElementById('preview-stamp'),
        introText: document.getElementById('preview-intro-text'),
    };

    // --- Funcții Helper pentru generare HTML ---
    const getClassicHeader = (color) => `
        <div style="border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px; vertical-align: middle;"><div style="width: 120px; height: 60px; background: #eee; text-align: center; line-height: 60px; font-size: 10px; color: #999;">Logo</div></td>
                    <td style="vertical-align: middle; font-size: 9px; line-height: 1.3;">
                        <p><strong>NUME FIRMĂ EXEMPLU SRL</strong></p>
                        <p>Reg. Com.: J12/345/2025 | C.I.F: RO123456</p>
                    </td>
                </tr>
            </table>
        </div>
        <div style="text-align: right; margin-bottom: 20px;">
            <h2 style="color: ${color}; margin: 0;">${controls.title.value}</h2>
            <p style="font-size: 10px;">Nr: AAA-101 | Data: 29.10.2025</p>
        </div>`;

    const getModernHeader = (color) => `
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 120px; height: 60px; background: #eee; text-align: center; line-height: 60px; font-size: 10px; color: #999; margin: 0 auto 15px;">Logo</div>
            <h2 style="color: ${color}; margin: 0;">${controls.title.value}</h2>
            <p style="font-size: 10px;">Nr: AAA-101 | Data: 29.10.2025</p>
            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; font-size: 9px; line-height: 1.3; margin-top: 15px;">
                <p><strong>NUME FIRMĂ EXEMPLU SRL</strong> | Reg. Com.: J12/345/2025 | C.I.F: RO123456</p>
            </div>
        </div>`;
    
    const getCompactHeader = (color) => `
        <div style="border-top: 5px solid ${color}; padding-top: 10px; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 70%; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 130px; vertical-align: middle;"><div style="width: 120px; height: 60px; background: #eee; text-align: center; line-height: 60px; font-size: 10px; color: #999;">Logo</div></td>
                                <td style="vertical-align: middle; font-size: 9px; line-height: 1.2; padding-left: 10px;">
                                    <p><strong>NUME FIRMĂ EXEMPLU SRL</strong></p>
                                    <p>Reg. Com.: J12/345/2025 | C.I.F: RO123456</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 30%; vertical-align: top; text-align: right;">
                        <h2 style="color: ${color}; margin: 0;">${controls.title.value}</h2>
                        <p style="font-size: 10px;">Nr: AAA-101 | Data: 29.10.2025</p>
                    </td>
                </tr>
            </table>
        </div>`;
    
    const getElegantHeader = (color) => `
        <div style="margin-bottom: 30px; text-align: right;">
            <h1 style="color: ${color}; margin: 0; font-family: 'Merriweather', serif; font-weight: bold;">${controls.title.value}</h1>
            <p style="font-size: 10px; margin-top: 5px;">Nr: AAA-101 / Data: 29.10.2025</p>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
             <tr>
                <td style="font-size: 9px; line-height: 1.3;">
                    <p style="text-transform: uppercase; color: #999;">Furnizor:</p>
                    <p><strong>NUME FIRMĂ EXEMPLU SRL</strong><br>Reg. Com.: J12/345/2025 | C.I.F: RO123456</p>
                </td>
             </tr>
        </table>
        `;
    
    const getMinimalistHeader = (color) => `
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
             <tr>
                <td>
                    <h2 style="color: ${color}; margin: 0;">${controls.title.value}</h2>
                </td>
                <td style="text-align: right; font-size: 10px;">
                    <p><strong>NUME FIRMĂ EXEMPLU SRL</strong></p>
                    <p>Nr: AAA-101 | Data: 29.10.2025</p>
                </td>
             </tr>
        </table>
        <div style="border-bottom: 2px solid ${color}; margin-bottom: 20px;"></div>
    `;

    // --- Funcția Principală de Update ---
    function updatePreview() {
        const layout = controls.layout.value;
        const color = controls.color.value;

        // Font & Color Globale
        preview.page.style.fontFamily = `"${controls.font.value}", sans-serif`;
        preview.tableHead.style.backgroundColor = color;
        preview.tableHead.style.color = 'white';
        preview.tableHead.style.borderColor = color;

        // Footer Text
        preview.footer.textContent = controls.footer.value || 'Termenii și condițiile vor apărea aici.';

        // NOU: Actualizare text introductiv
        if(controls.introText && preview.introText) {
            let text = controls.introText.value;
            // Înlocuim variabilele cu valori exemplu
            text = text.replace(/{obiect}/g, 'Renovare apartament Exemplu')
                       .replace(/{client}/g, 'NUME CLIENT EXEMPLU SRL')
                       .replace(/{total_fara_tva}/g, '1.500,00')
                       .replace(/{tva}/g, '285,00')
                       .replace(/{total_cu_tva}/g, '1.785,00')
                       .replace(/\n/g, '<br>'); // Convertim liniile noi în tag-uri <br>

            preview.introText.innerHTML = text;
        }
        
        // Table Style
        preview.table.classList.remove('table-bordered', 'table-striped');
        preview.stripedRow.style.backgroundColor = 'transparent';
        if (controls.tableStyle.value === 'grid') {
            preview.table.classList.add('table-bordered');
        } else {
            preview.stripedRow.style.backgroundColor = '#f8f9fa';
        }
        
        // Generează HTML-ul header-ului pe baza layout-ului selectat
        if (layout === 'classic') preview.headerContainer.innerHTML = getClassicHeader(color);
        else if (layout === 'modern') preview.headerContainer.innerHTML = getModernHeader(color);
        else if (layout === 'compact') preview.headerContainer.innerHTML = getCompactHeader(color);
        else if (layout === 'elegant') preview.headerContainer.innerHTML = getElegantHeader(color);
        else if (layout === 'minimalist') preview.headerContainer.innerHTML = getMinimalistHeader(color);

        // Aici era greșeala: am înlocuit `controls.stamp_size` cu `controls.stampSize`
        const size = controls.stampSize.value;
        preview.stamp.style.width = size + 'px';
        if (stampSizeValue) {
            stampSizeValue.textContent = size + 'px';
        }
    }
    // Listener pentru încărcarea instantanee a previzualizării ștampilei
    if (controls.stamp && !controls.stamp.listenerAttached) {
        controls.stamp.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.stamp.src = e.target.result;
                    preview.stamp.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        controls.stamp.listenerAttached = true;
    }

    // Attach event listeners
    Object.values(controls).forEach(control => {
        if (!control.listenerAttached) {
            control.addEventListener('input', updatePreview);
            control.listenerAttached = true;
        }
    });

    // Update inițial
    updatePreview();
}

function handleQuickStatusUpdate() {
    const container = document.querySelector('#offers-table-container');
    if (!container) return;

    if (!container.listenerAttached) {
        container.addEventListener('click', function(event) {
            const target = event.target;
            
            if (target.classList.contains('update-status-btn')) {
                event.preventDefault();

                const offerId = target.dataset.offerId;
                const newStatus = target.dataset.newStatus;
                const url = target.dataset.url;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                if (newStatus === 'Finalizata') {
                    const checkUrl = `/oferte/${offerId}/verifica-situatie-plata`;
                    
                    fetch(checkUrl)
                        .then(res => res.json())
                        .then(data => {
                            if (data.has_statement) {
                                showToast('Oferta are deja o situație de plată.', 'info');
                                updateStatusAjax(url, newStatus, csrfToken, offerId);
                            } else {
                                // Dacă nu există, afișăm modal-ul de confirmare
                                const createUrl = `/oferte/${offerId}/creeaza-situatie-plata`;
                                const modalElement = document.getElementById('createPaymentStatementModal');
                                if (modalElement) {
                                    const modal = new Modal(modalElement);
                                    const confirmBtn = document.getElementById('confirmCreateStatementBtn');
                                    confirmBtn.href = createUrl;

                                    // NOU: Adăugăm un listener pentru a închide modal-ul la click
                                    confirmBtn.addEventListener('click', () => {
                                        modal.hide();
                                    }, { once: true });

                                    modal.show();
                                    
                                    modalElement.addEventListener('hidden.bs.modal', (event) => {
                                        // Rulăm update-ul doar dacă nu am navigat deja
                                        if (!event.relatedTarget || event.relatedTarget !== confirmBtn) {
                                            updateStatusAjax(url, newStatus, csrfToken, offerId);
                                        }
                                    }, { once: true });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Eroare la verificare:', error);
                            showToast('A apărut o eroare la verificare.', 'error');
                        });
                    
                    return; 
                }

                updateStatusAjax(url, newStatus, csrfToken, offerId);
             }
        });
        container.listenerAttached = true;
    }
}

function updateStatusAjax(url, newStatus, csrfToken, offerId) {
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => {
        if (!response.ok) {
            // Aruncăm o eroare dacă răspunsul serverului nu este 2xx
            throw new Error('Răspunsul serverului nu a fost OK');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const statusCell = document.getElementById(`status-cell-${offerId}`);
            if (statusCell) {
                const dropdownToggle = statusCell.querySelector('.dropdown-toggle');
                if (dropdownToggle) {
                    dropdownToggle.textContent = newStatus;
                    dropdownToggle.className = 'badge dropdown-toggle text-decoration-none ' + data.new_status_class;
                }
            }
            // Nu mai afișăm toast aici, pentru a nu dubla mesajele. Toast-ul va fi gestionat
            // de funcția principală, dacă este necesar.
        } else {
            showToast(data.error || 'A apărut o eroare la actualizarea statusului.', 'error');
        }
    })
    .catch(error => {
        console.error('Eroare AJAX la updateStatus:', error);
        showToast('Eroare de rețea la actualizarea statusului.', 'error');
    });
}

// NOUA Versiune pentru a afișa notificarea de succes
function handleSuccessToast() {
    const body = document.body;
    const successMessage = body.dataset.successMessage;

    if (successMessage) {
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer) {
            const toastHTML = `
                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"><i class="fa-solid fa-check-circle me-2"></i>${successMessage}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>`;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            const newToastEl = toastContainer.lastElementChild;

            if (newToastEl) {
                const toast = new Toast(newToastEl, { delay: 2500 });
                newToastEl.addEventListener('hidden.bs.toast', () => newToastEl.remove());
                toast.show();
            }
        }
        // Curățăm atributul pentru a nu mai fi declanșat la navigările Swup
        delete body.dataset.successMessage;
    }
}

// NOUA Versiune pentru a afișa notificarea de eroare
function handleErrorToast() {
    const body = document.body;
    const errorMessage = body.dataset.errorMessage;

    if (errorMessage) {
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer) {
            const toastHTML = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"><i class="fa-solid fa-exclamation-triangle me-2"></i>${errorMessage}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>`;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            const newToastEl = toastContainer.lastElementChild;

            if (newToastEl) {
                const toast = new Toast(newToastEl, { delay: 4000 });
                newToastEl.addEventListener('hidden.bs.toast', () => newToastEl.remove());
                toast.show();
            }
        }
        // Curățăm atributul
        delete body.dataset.errorMessage;
    }
}

function handleNumberingModeToggle() {
    const autoModeRadio = document.getElementById('mode_auto');
    const manualModeRadio = document.getElementById('mode_manual');
    const autoOptionsDiv = document.getElementById('auto-numbering-options');

    if (!autoModeRadio || !manualModeRadio || !autoOptionsDiv) {
        return;
    }

    function toggleOptions() {
        autoOptionsDiv.style.display = autoModeRadio.checked ? 'block' : 'none';
    }

    if (!autoModeRadio.listenerAttached) {
        autoModeRadio.addEventListener('change', toggleOptions);
        autoModeRadio.listenerAttached = true;
    }
    if (!manualModeRadio.listenerAttached) {
        manualModeRadio.addEventListener('change', toggleOptions);
        manualModeRadio.listenerAttached = true;
    }
    
    // Rulează la încărcare pentru a seta starea corectă
    toggleOptions();
}

function handleCuiApiSearch() {
    const searchBtn = document.getElementById('cui-search-btn');
    const searchInput = document.getElementById('cui-search-input');
    
    // Rulează codul doar dacă elementele necesare există pe pagina curentă
    if (!searchBtn || !searchInput) {
        return;
    }

    const apiResult = document.getElementById('api-result');
    const formFields = {
        name: document.getElementById('name'),
        vat_number: document.getElementById('vat_number'),
        trade_register_number: document.getElementById('trade_register_number'),
        address: document.getElementById('address')
    };

    const searchFunction = () => {
        const cui = searchInput.value.trim().toUpperCase().replace('RO', '');
        if (!cui) {
            apiResult.textContent = 'Vă rugăm introduceți un CUI.';
            apiResult.className = 'mt-2 small text-danger';
            return;
        }

        apiResult.textContent = 'Se caută...';
        apiResult.className = 'mt-2 small text-muted';
        
        // Asigură-te că folosești protocolul corect (https)
        fetch(`https://lista-firme.info/api/v1/info?cui=${cui}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Răspunsul rețelei nu a fost ok.');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.name) {
                    const { name, cui: cuiVal, reg_com, address = {} } = data;
                    
                    formFields.name.value = name || '';
                    formFields.vat_number.value = `RO${cuiVal}` || '';
                    formFields.trade_register_number.value = reg_com || '';

                    const adresaParts = [];
                    if (address.county) adresaParts.push(address.county);
                    if (address.city) adresaParts.push(address.city);
                    if (address.street) adresaParts.push(`Str. ${address.street}`);
                    if (address.number) adresaParts.push(`Nr. ${address.number}`);
                    formFields.address.value = adresaParts.join(', ');

                    apiResult.textContent = `Firmă găsită: ${name}. Câmpurile au fost pre-completate.`;
                    apiResult.className = 'mt-2 small text-success';
                } else {
                    apiResult.textContent = "Firmă negăsită sau CUI invalid.";
                    apiResult.className = 'mt-2 small text-danger';
                }
            })
            .catch(error => {
                console.error('API Error:', error);
                apiResult.textContent = "A apărut o eroare la interogarea API-ului. Vă rugăm verificați consola.";
                apiResult.className = 'mt-2 small text-danger';
            });
    };

    // Adăugăm ascultători de evenimente doar o singură dată
    if (!searchBtn.listenerAttached) {
        searchBtn.addEventListener('click', searchFunction);
        searchBtn.listenerAttached = true;
    }
    if (!searchInput.listenerAttached) {
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Previne trimiterea formularului
                searchFunction();
            }
        });
        searchInput.listenerAttached = true;
    }
}

// NOU: Funcția care desenează graficele de pe pagina de rapoarte
function handleReportCharts() {
    // Folosim setTimeout cu 0ms pentru a împinge execuția la următorul "tick" al browser-ului.
    // Asta garantează că DOM-ul este complet randat și dimensionat înainte de a încerca să desenăm.
    setTimeout(() => {
        // PASUL 1: Distrugem orice instanță de grafic existentă pentru a preveni conflictele
        activeCharts.forEach(chart => chart.destroy());
        activeCharts = []; // Resetăm array-ul

        const reportsDataElement = document.getElementById('reports-data');
        if (!reportsDataElement) {
            return;
        }

        const data = JSON.parse(reportsDataElement.innerHTML);

        // Citim culorile temei direct din CSS
        const themeStyles = getComputedStyle(document.documentElement);
        const textColor = themeStyles.getPropertyValue('--text-secondary').trim();
        const borderColor = themeStyles.getPropertyValue('--border-color').trim();
        const primaryAccentColor = themeStyles.getPropertyValue('--primary-accent').trim();

        const statusColors = {
            'Draft': '#6c757d', 'Trimisa': '#0dcaf0', 'Acceptata': '#198754',
            'Respinsa': '#dc3545', 'Anulata': '#adb5bd', 'Facturata': '#0d6efd',
            'Incasata': '#ffc107', 'Negociere': '#fd7e14'
        };
        
        // --- GRAFIC 1: STATUSURI (DONUT) ---
        const statusCtx = document.getElementById('statusDonutChart');
        if (statusCtx) {
            const statusLabels = Object.keys(data.statusDistribution);
            if(statusLabels.length > 0) {
                const statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            label: 'Nr. Oferte', data: Object.values(data.statusDistribution),
                            backgroundColor: statusLabels.map(label => statusColors[label] || '#cccccc'),
                            borderColor: themeStyles.getPropertyValue('--element-bg').trim(), borderWidth: 3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: textColor } } } }
                });
                activeCharts.push(statusChart);
            }
        }

        // --- GRAFIC 2: VALORI LUNARE (BAR) ---
        const monthlyCtx = document.getElementById('monthlyValuesBarChart');
        if (monthlyCtx) {
            if(Object.keys(data.monthlyChartData).length > 0) {
                const monthlyChart = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.monthlyChartData),
                        datasets: [{
                            label: 'Valoare totală (RON)', data: Object.values(data.monthlyChartData),
                            backgroundColor: primaryAccentColor + '80', borderColor: primaryAccentColor,
                            borderWidth: 1, borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: borderColor } }, x: { ticks: { color: textColor }, grid: { color: 'transparent' } } }, plugins: { legend: { display: false } } }
                });
                activeCharts.push(monthlyChart);
            }
        }

        // --- GRAFIC 3: TOP CLIENTI (BARA ORIZONTALA) ---
        const clientsCtx = document.getElementById('topClientsChart');
        if (clientsCtx) {
            if(Object.keys(data.topClients).length > 0) {
                const clientsChart = new Chart(clientsCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.topClients),
                        datasets: [{
                            label: 'Valoare totală oferte (RON)', data: Object.values(data.topClients),
                            backgroundColor: primaryAccentColor + '80', borderColor: primaryAccentColor, borderWidth: 1
                        }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { color: textColor }, grid: { color: borderColor } }, y: { ticks: { color: textColor }, grid: { color: 'transparent' } } } }
                });
                activeCharts.push(clientsChart);
            }
        }
    }, 0);
}

// --- FUNCȚIA PRINCIPALĂ DE INIȚIALIZARE ---
function initPage() {
    // Curățăm orice notificare rămasă de la o navigare anterioară
    document.querySelectorAll('.toast.shown').forEach(toast => toast.remove());
    
    handleSidebar();
    handleClientSearch();
    handleOfferSearch();
    handleOfferSettingsToggles();
    handleOfferForm();
    handleTemplateCreator();
    handleSuccessToast();
    handleErrorToast();
    handleNumberingModeToggle();
    handleCuiApiSearch();
    handleQuickStatusUpdate();
    handleQuickAssignUpdate();
    handleReportCharts();

    // Inițializăm toate modalele
    handleDeleteModal('deleteClientModal', 'confirmDeleteBtnClient', 'data-form-id');
    handleDeleteModal('deleteOfferModal', 'confirmDeleteBtnOffer', 'data-form-id');
    handleDeleteModal('deleteUserModal', 'confirmDeleteBtnUser', 'data-form-id');
}

// --- CONFIGURARE SWUP & EVENIMENTE ---
document.addEventListener('DOMContentLoaded', initPage);

const swup = new Swup({
    containers: ['#swup', '#sidebar-nav'],
    cache: false
});

// NOU: Hook pentru a curăța elementele Bootstrap înainte de tranziție
swup.hooks.on('visit:start', () => {
    // Închidem orice modal deschis
    const openModal = document.querySelector('.modal.show');
    if (openModal) {
        // Obținem instanța Bootstrap și o închidem corect
        const modalInstance = bootstrap.Modal.getInstance(openModal);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
    // Ștergem fundalul semi-transparent dacă a rămas blocat
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    // Scoatem clasa de pe body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});

// Hook care rulează DUPĂ ce noua pagină a fost încărcată
swup.hooks.on('visit:end', initPage);
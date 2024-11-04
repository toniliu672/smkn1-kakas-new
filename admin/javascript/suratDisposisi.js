// admin/javascript/suratDisposisi.js

// ==========================================
// Constants & DOM Elements
// ==========================================
const DOM = {
    table: document.getElementById("suratTable"),
    modal: {
        surat: document.getElementById("suratModal"),
        approval: document.getElementById("approvalModal")
    },
    form: {
        surat: document.getElementById("suratForm"),
        approval: document.getElementById("approvalForm")
    },
    button: {
        tambah: document.getElementById("btnTambah"),
        closeModals: document.querySelectorAll(".closeModal")
    },
    filter: {
        tanggalMulai: document.getElementById("filterTanggalMulai"),
        tanggalAkhir: document.getElementById("filterTanggalAkhir"),
        status: document.getElementById("filterStatus")
    }
};

// ==========================================
// Utility Functions
// ==========================================
const utils = {
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric"
        });
    },

    getStatusBadge(status) {
        const badges = {
            pending: '<span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            approved: '<span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Disetujui</span>',
            rejected: '<span class="px-2 py-1 rounded-full bg-red-100 text-red-800">Ditolak</span>'
        };
        return badges[status] || status;
    },

    showToast(type, message) {
        $.toast({
            heading: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            showHideTransition: "slide",
            icon: type,
            position: "top-right"
        });
    }
};

// ==========================================
// UI Handlers
// ==========================================
const uiHandlers = {
    populateEditForm(surat) {
        document.getElementById('suratId').value = surat.id;
        document.getElementById('nomorSurat').value = surat.nomor_surat;
        document.getElementById('tanggalSurat').value = surat.tanggal_surat;
        document.getElementById('tanggalDiterima').value = surat.tanggal_diterima;
        document.getElementById('tujuanSurat').value = surat.tujuan_surat;
        
        document.getElementById('modalTitle').textContent = 'Edit Surat Disposisi';
        
        // Update file info
        const fileInfo = document.getElementById('fileInfo');
        if (fileInfo) {
            if (surat.file_surat) {
                const fileName = surat.file_surat.split('/').pop();
                fileInfo.textContent = `File saat ini: ${fileName}`;
            } else {
                fileInfo.textContent = 'Tidak ada file';
            }
        }
    },

    resetForm() {
        DOM.form.surat.reset();
        document.getElementById('suratId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Surat Disposisi';
        const fileInfo = document.getElementById('fileInfo');
        if (fileInfo) fileInfo.textContent = '';
    },

    getActionButtons(surat) {
        let buttons = "";
        
        if (surat.status === "pending") {
            if (document.body.dataset.userRole === "kepala_sekolah") {
                buttons += `
                    <button data-id="${surat.id}" class="approve-btn text-green-600 hover:text-green-800">
                        <i class="fas fa-check"></i>
                    </button>
                    <button data-id="${surat.id}" class="reject-btn text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            } else if (["admin", "pengelola_surat"].includes(document.body.dataset.userRole)) {
                buttons += `
                    <button data-id="${surat.id}" class="edit-btn text-yellow-600 hover:text-yellow-800 mr-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button data-id="${surat.id}" class="delete-btn text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }
        }
        
        return buttons;
    },

    async loadTableData() {
        try {
            const params = new URLSearchParams({
                tanggal_mulai: DOM.filter.tanggalMulai.value,
                tanggal_akhir: DOM.filter.tanggalAkhir.value,
                status: DOM.filter.status.value
            });
    
            const response = await fetch(`../functions/surat-disposisi.php?${params}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await response.json();
    
            const tbody = DOM.table.querySelector("tbody");
            tbody.innerHTML = "";
    
            data.forEach((surat, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${index + 1}
                    </td>
                    <td class="px-6 py-4">${surat.nomor_surat}</td>
                    <td class="px-6 py-4">${utils.formatDate(surat.tanggal_surat)}</td>
                    <td class="px-6 py-4">${utils.formatDate(surat.tanggal_diterima)}</td>
                    <td class="px-6 py-4">${surat.tujuan_surat}</td>
                    <td class="px-6 py-4">${utils.getStatusBadge(surat.status)}</td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="${surat.file_surat}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                            ${this.getActionButtons(surat)}
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
    
            eventListeners.attachActionButtons();
        } catch (error) {
            console.error("Error:", error);
            utils.showToast("error", "Gagal memuat data: " + error.message);
        }
    }
};

// ==========================================
// Event Listeners
// ==========================================
const eventListeners = {
    initializeAll() {
        // Tambah button
        if (DOM.button.tambah) {
            DOM.button.tambah.addEventListener("click", () => {
                uiHandlers.resetForm();
                DOM.modal.surat.classList.remove("hidden");
            });
        }

        // Close buttons
        DOM.button.closeModals.forEach((button) => {
            button.addEventListener("click", () => {
                DOM.modal.surat.classList.add("hidden");
                DOM.modal.approval?.classList.add("hidden");
            });
        });

        // Form submission handler
        DOM.form.surat.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(DOM.form.surat);
            const suratId = document.getElementById('suratId').value;
            formData.append("action", suratId ? "update" : "create");

            try {
                const response = await fetch('../functions/surat-disposisi.php', {
                    method: 'POST',
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });
                const result = await response.json();
                
                if (result.status) {
                    utils.showToast("success", result.message);
                    DOM.modal.surat.classList.add("hidden");
                    uiHandlers.loadTableData();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                utils.showToast("error", error.message);
            }
        });

        // Filter handlers
        Object.values(DOM.filter).forEach(filter => {
            filter.addEventListener('change', () => uiHandlers.loadTableData());
        });
    },

    attachActionButtons() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                try {
                    const response = await fetch(`../functions/surat-disposisi.php?action=get&id=${btn.dataset.id}`, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                    
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    
                    const data = await response.json();
                    if (!data) throw new Error('Data tidak ditemukan');
                    
                    uiHandlers.populateEditForm(data);
                    DOM.modal.surat.classList.remove('hidden');
                } catch (error) {
                    utils.showToast('error', 'Gagal memuat data surat: ' + error.message);
                }
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (confirm('Apakah Anda yakin ingin menghapus surat ini?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', btn.dataset.id);
                    
                    try {
                        const response = await fetch('../functions/surat-disposisi.php', {
                            method: 'POST',
                            body: formData,
                            headers: { "X-Requested-With": "XMLHttpRequest" }
                        });
                        const result = await response.json();
                        
                        if (result.status) {
                            utils.showToast('success', result.message);
                            uiHandlers.loadTableData();
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        utils.showToast('error', error.message);
                    }
                }
            });
        });
    }
};

// ==========================================
// Initialize Application
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    eventListeners.initializeAll();
    uiHandlers.loadTableData();
});
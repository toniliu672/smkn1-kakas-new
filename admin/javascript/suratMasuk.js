// admin/javascript/suratMasuk.js

const DOM = {
    table: document.getElementById("suratTable"),
    modal: {
        keluarkan: document.getElementById("keluarkanModal")
    },
    form: {
        keluarkan: document.getElementById("keluarkanForm")
    },
    button: {
        closeModals: document.querySelectorAll(".closeModal")
    },
    filter: {
        tanggalMulai: document.getElementById("filterTanggalMulai"),
        tanggalAkhir: document.getElementById("filterTanggalAkhir"),
        status: document.getElementById("filterStatus")
    }
};

const utils = {
    formatDate(dateString) {
        if (!dateString) return "";
        return new Date(dateString).toLocaleDateString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric"
        });
    },

    formatDateTime(dateString) {
        if (!dateString) return "";
        return new Date(dateString).toLocaleString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });
    },
    formatKeterangan(surat) {
        // Jika status active dan ada catatan pembatalan, tampilkan catatan pembatalan
        if (surat.status === 'active' && surat.keterangan_pembatalan) {
            return `
                <div class="text-sm text-red-600">
                    <div class="font-medium">Dibatalkan:</div>
                    <div class="whitespace-pre-wrap">${surat.keterangan_pembatalan}</div>
                </div>
            `;
        }
        return '-';
    },

    getStatusBadge(status, keterangan = '') {
        const badges = {
            active: `
                <div>
                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Aktif</span>
                    ${this.formatKeterangan(keterangan)}
                </div>
            `,
            exported: `
                <div>
                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800">Sudah Dikeluarkan</span>
                    ${this.formatKeterangan(keterangan)}
                </div>
            `
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

const uiHandlers = {
    async loadTableData() {
        try {
            const params = new URLSearchParams({
                tanggal_mulai: DOM.filter.tanggalMulai.value,
                tanggal_akhir: DOM.filter.tanggalAkhir.value,
                status: DOM.filter.status.value
            });

            const response = await fetch(`../functions/surat-masuk.php?${params}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.populateTable(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error("Error:", error);
            utils.showToast("error", "Gagal memuat data: " + error.message);
            this.populateTable([]);
        }
    },

    populateTable(data) {
        const tbody = DOM.table.querySelector("tbody");
        tbody.innerHTML = "";

        data.forEach((surat, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${index + 1}</td>
                <td class="px-6 py-4">${surat.nomor_surat || '-'}</td>
                <td class="px-6 py-4">${utils.formatDate(surat.tanggal_surat)}</td>
                <td class="px-6 py-4">${utils.formatDateTime(surat.tanggal_persetujuan)}</td>
                <td class="px-6 py-4">${surat.tujuan_surat}</td>
                <td class="px-6 py-4">${utils.getStatusBadge(surat.status)}</td>
                <td class="px-6 py-4">${utils.formatKeterangan(surat)}</td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        ${this.getActionButtons(surat)}
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        this.attachActionButtons();
    },

    getActionButtons(surat) {
        let buttons = `
            <a href="${surat.file_surat}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-2" title="Lihat Surat">
                <i class="fas fa-eye"></i>
            </a>`;

        if (["admin", "pengelola_surat"].includes(document.body.dataset.userRole)) {
            if (surat.status === "active") {
                buttons += `
                    <button data-id="${surat.id}" data-nomor="${surat.nomor_surat}" 
                        data-file="${surat.file_surat}"
                        class="keluarkan-btn text-blue-600 hover:text-blue-800 mr-2" 
                        title="Keluarkan Surat">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <button data-id="${surat.id}" class="delete-btn text-red-600 hover:text-red-800"
                        title="Hapus Surat">
                        <i class="fas fa-trash"></i>
                    </button>`;
            }
        }

        return buttons;
    },

    attachActionButtons() {
        // Keluarkan buttons
        document.querySelectorAll('.keluarkan-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nomorSurat = btn.dataset.nomor;
                const fileSurat = btn.dataset.file;

                document.getElementById('suratMasukId').value = id;
                document.getElementById('nomorSuratKeluar').value = `KEL/${nomorSurat}`;
                document.getElementById('tanggalKeluar').value = new Date().toISOString().slice(0, 16);
                document.getElementById('fileSurat').value = fileSurat; // Set file path yang sudah ada
                
                DOM.modal.keluarkan.classList.remove('hidden');
            });
        });

        // Delete buttons dengan SweetAlert2
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                console.log('Delete button clicked'); // Debug log
                
                const result = await Swal.fire({
                    title: 'Konfirmasi Hapus Surat',
                    html: `
                        <p class="mb-2">Apakah Anda yakin ingin menghapus surat ini?</p>
                        <div class="text-left text-sm bg-yellow-50 border border-yellow-200 p-3 rounded">
                            <p class="font-medium text-yellow-800">Perhatian:</p>
                            <ul class="list-disc list-inside text-yellow-700">
                                <li>Surat masuk akan dihapus</li>
                                <li>Status surat disposisi akan kembali ke pending</li>
                                <li>Tindakan ini tidak dapat dibatalkan</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                });

                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', btn.dataset.id);

                        const response = await fetch('../functions/surat-masuk.php', {
                            method: 'POST',
                            body: formData,
                            headers: { "X-Requested-With": "XMLHttpRequest" }
                        });

                        const result = await response.json();

                        if (result.status) {
                            utils.showToast('success', result.message);
                            this.loadTableData();
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        utils.showToast('error', error.message);
                        console.error('Error:', error);
                    }
                }
            });
        });
    }
};

// Initialize Application
document.addEventListener("DOMContentLoaded", () => {
    // Initialize all event listeners
    DOM.button.closeModals.forEach((button) => {
        button.addEventListener("click", () => {
            DOM.modal.keluarkan.classList.add("hidden");
            DOM.form.keluarkan.reset();
        });
    });

    // Keluarkan form submission
    DOM.form.keluarkan.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(DOM.form.keluarkan);
        formData.append("action", "create");

        try {
            const response = await fetch("../functions/surat-keluar.php", {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const result = await response.json();

            if (result.status) {
                utils.showToast("success", result.message);
                DOM.modal.keluarkan.classList.add("hidden");
                DOM.form.keluarkan.reset();
                uiHandlers.loadTableData();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            utils.showToast("error", error.message);
        }
    });

    // Filter handlers
    Object.values(DOM.filter).forEach((filter) => {
        filter.addEventListener("change", () => uiHandlers.loadTableData());
    });

    // Load initial data
    uiHandlers.loadTableData();
});
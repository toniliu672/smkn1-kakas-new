// admin/javascript/suratKeluar.js

const DOM = {
    table: document.getElementById("suratTable"),
    modal: {
        cancel: document.getElementById("cancelModal")
    },
    form: {
        cancel: document.getElementById("cancelForm")
    },
    button: {
        closeModals: document.querySelectorAll(".closeModal")
    },
    filter: {
        tanggalMulai: document.getElementById("filterTanggalMulai"),
        tanggalAkhir: document.getElementById("filterTanggalAkhir"),
        status: document.getElementById("filterStatus")
    },
    stats: {
        total: document.getElementById("totalSurat"),
        active: document.getElementById("activeSurat"),
        cancelled: document.getElementById("cancelledSurat")
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

    getStatusBadge(status) {
        const badges = {
            active: '<span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Aktif</span>',
            cancelled: '<span class="px-2 py-1 rounded-full bg-red-100 text-red-800">Dibatalkan</span>'
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

            // Get table data
            let data = [];
            const dataResponse = await fetch(`../functions/surat-keluar.php?${params}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            
            if (!dataResponse.ok) {
                throw new Error(`HTTP error! status: ${dataResponse.status}`);
            }
            const dataText = await dataResponse.text();
            if (dataText) {
                data = JSON.parse(dataText);
            }

            // Get stats
            let stats = null;
            const statsResponse = await fetch(`../functions/surat-keluar.php?action=stats&${params}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            
            if (!statsResponse.ok) {
                throw new Error(`HTTP error! status: ${statsResponse.status}`);
            }
            const statsText = await statsResponse.text();
            if (statsText) {
                stats = JSON.parse(statsText);
            }

            // Update UI
            if (stats) {
                this.updateStats(stats);
            }
            this.populateTable(Array.isArray(data) ? data : []);
            
        } catch (error) {
            console.error("Error:", error);
            utils.showToast("error", "Gagal memuat data: " + error.message);
            this.populateTable([]);
            this.updateStats({
                total_surat: 0,
                active_surat: 0,
                cancelled_surat: 0
            });
        }
    },

    updateStats(stats) {
        if (stats) {
            DOM.stats.total.textContent = stats.total_surat || 0;
            DOM.stats.active.textContent = stats.active_surat || 0;
            DOM.stats.cancelled.textContent = stats.cancelled_surat || 0;
        }
    },

    populateTable(data) {
        const tbody = DOM.table.querySelector("tbody");
        tbody.innerHTML = "";

        if (!data.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data yang tersedia
                    </td>
                </tr>
            `;
            return;
        }

        data.forEach((surat, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${index + 1}</td>
                <td class="px-6 py-4">${surat.nomor_surat_keluar}</td>
                <td class="px-6 py-4">${surat.nomor_surat_asal}</td>
                <td class="px-6 py-4">${utils.formatDateTime(surat.tanggal_keluar)}</td>
                <td class="px-6 py-4">${surat.dikeluarkan_oleh_name}</td>
                <td class="px-6 py-4">${utils.getStatusBadge(surat.status)}</td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        ${this.getActionButtons(surat)}
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        eventListeners.attachActionButtons();
    },

    getActionButtons(surat) {
        let buttons = `
            <a href="${surat.file_surat_keluar}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-2" title="Lihat Surat">
                <i class="fas fa-eye"></i>
            </a>`;

        if (surat.status === "active" && ["admin", "pengelola_surat"].includes(document.body.dataset.userRole)) {
            buttons += `
                <button data-id="${surat.id}" class="cancel-btn text-red-600 hover:text-red-800" title="Batalkan Surat">
                    <i class="fas fa-times"></i>
                </button>`;
        }

        return buttons;
    }
};

const eventListeners = {
    initializeAll() {
        // Close buttons
        DOM.button.closeModals.forEach(button => {
            button.addEventListener("click", () => {
                DOM.modal.cancel.classList.add("hidden");
                DOM.form.cancel.reset();
            });
        });

        // Cancel form submission
        DOM.form.cancel.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(DOM.form.cancel);

            try {
                const response = await fetch("../functions/surat-keluar.php", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });
                const result = await response.json();

                if (result.status) {
                    utils.showToast("success", result.message);
                    DOM.modal.cancel.classList.add("hidden");
                    DOM.form.cancel.reset();
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
            filter.addEventListener("change", () => uiHandlers.loadTableData());
        });
    },

    attachActionButtons() {
        // Cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('cancelSuratId').value = btn.dataset.id;
                DOM.modal.cancel.classList.remove('hidden');
            });
        });
    }
};

// Initialize Application
document.addEventListener("DOMContentLoaded", () => {
    eventListeners.initializeAll();
    uiHandlers.loadTableData();
});
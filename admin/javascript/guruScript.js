// admin/javascript/guruScript.js
$(document).ready(function () {
  let currentPage = 1;
  let searchParams = {};

  // Initialize DataTable with Search and Pagination
  function initializeTable() {
    loadData();

    // Search input handler
    $("#searchInput").on(
      "keyup",
      debounce(function () {
        searchParams.nama = $(this).val();
        currentPage = 1;
        loadData();
      }, 500)
    );

    // Filter handlers
    $(".filter-select").on("change", function () {
      searchParams[$(this).data("filter")] = $(this).val();
      currentPage = 1;
      loadData();
    });

    // Pagination handlers
    $(document).on("click", ".pagination-btn", function () {
      if (!$(this).hasClass("disabled")) {
        currentPage = parseInt($(this).data("page"));
        loadData();
      }
    });

    // Items per page handler
    $("#itemsPerPage").on("change", function () {
      currentPage = 1;
      loadData();
    });
  }

  // Load Data Function
  function loadData() {
    const limit = parseInt($("#itemsPerPage").val());

    $.ajax({
      url: "../functions/guru/",
      type: "POST",
      data: {
        action: "getGuru",
        page: currentPage,
        limit: limit,
        search: searchParams,
      },
      success: function (response) {
        console.log("Raw response:", response);

        try {
          const result =
            typeof response === "string" ? JSON.parse(response) : response;
          console.log("Parsed response:", result);

          if (result.status === "success") {
            if (Array.isArray(result.data)) {
              updateTable(result.data);
              updatePagination(result.total, limit);
            } else {
              showToast("error", "Format data tidak valid");
            }
          } else {
            showToast("error", result.message || "Terjadi kesalahan");
          }
        } catch (e) {
          console.error("Error processing response:", e);
          showToast("error", "Gagal memproses data: " + e.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", { xhr, status, error });
        showToast("error", "Gagal memuat data: " + error);
      },
    });
  }

  // Update Table Function
  function updateTable(data) {
    const tbody = $('#dataTable tbody');
    tbody.empty();
    
    if (!Array.isArray(data) || data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
            </tr>
        `);
        return;
    }
    
    data.forEach((item, index) => {
        const rowNumber = (currentPage - 1) * parseInt($('#itemsPerPage').val()) + index + 1;
        
        const actions = `
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-1 view-btn" data-id="${item.id}">
                Lihat
            </button>
            <button class=" px-3 py-1 rounded text-sm mr-1 edit-btn" data-id="${item.id}">
                Edit
            </button>
            <button class="text-red-500  px-3 py-1 rounded text-sm delete-btn" data-id="${item.id}">
                Hapus
            </button>`;

        tbody.append(`
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">${rowNumber}</td>
                <td class="px-4 py-3">${item.nip || '-'}</td>
                <td class="px-4 py-3">${item.nama_lengkap}</td>
                <td class="px-4 py-3">${item.kontak || '-'}</td>
                <td class="px-4 py-3">${item.status}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-sm ${
                        item.status_aktif === 'aktif' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                    }">
                        ${item.status_aktif === 'aktif' ? 'Aktif' : 'Non-aktif'}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">${actions}</td>
            </tr>
        `);
    });
}

  // Update Pagination Function
  function updatePagination(total, limit) {
    const totalPages = Math.ceil(total / limit);
    const pagination = $(".pagination");
    pagination.empty();

    // Previous button
    pagination.append(`
        <button class="pagination-btn ${
          currentPage === 1
            ? "bg-gray-100 text-gray-400 cursor-not-allowed"
            : "bg-white hover:bg-gray-100"
        } 
                      px-3 py-1 rounded border" 
                data-page="${currentPage - 1}" 
                ${currentPage === 1 ? "disabled" : ""}>
          <i class="fas fa-chevron-left"></i>
        </button>
      `);

    // Page buttons
    for (let i = 1; i <= totalPages; i++) {
      if (
        i === 1 ||
        i === totalPages ||
        (i >= currentPage - 2 && i <= currentPage + 2)
      ) {
        pagination.append(`
            <button class="pagination-btn ${
              i === currentPage
                ? "bg-sky-500 text-white"
                : "bg-white hover:bg-gray-100"
            } 
                          px-3 py-1 rounded border"
                    data-page="${i}">
              ${i}
            </button>
          `);
      } else if (i === currentPage - 3 || i === currentPage + 3) {
        pagination.append(`<span class="px-2">...</span>`);
      }
    }

    // Next button
    pagination.append(`
        <button class="pagination-btn ${
          currentPage === totalPages
            ? "bg-gray-100 text-gray-400 cursor-not-allowed"
            : "bg-white hover:bg-gray-100"
        } 
                      px-3 py-1 rounded border" 
                data-page="${currentPage + 1}" 
                ${currentPage === totalPages ? "disabled" : ""}>
          <i class="fas fa-chevron-right"></i>
        </button>
      `);

    $("#totalData").text(total);
  }

  // Delete Handler
  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");

    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
      $.ajax({
        url: "../functions/guru/",
        type: "POST",
        data: {
          action: "deleteGuru",
          id: id,
        },
        success: function (response) {
          try {
            if (typeof response === "string") {
              response = JSON.parse(response);
            }
            if (response.status === "success") {
              showToast("success", response.message);
              loadData();
            } else {
              showToast("error", response.message);
            }
          } catch (e) {
            showToast("error", "Format response tidak valid");
          }
        },
        error: function (xhr, status, error) {
          showToast("error", "Gagal menghapus data: " + error);
        },
      });
    }
  });

  // View Handler
  $(document).on('click', '.view-btn', function() {
    const id = $(this).data('id');
    
    $.ajax({
        url: '../functions/guru/',
        type: 'POST',
        data: {
            action: 'getDetailGuru',
            id: id
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.status === 'error') {
                    showToast('error', data.message);
                    return;
                }
                
                const content = `
                    <div class="p-4">
                        ${data.foto ? `
                            <div class="mb-4">
                                <p class="font-semibold">Foto:</p>
                                <img src="../../${data.foto}" alt="Foto Guru" class="w-32 h-32 object-cover rounded">
                            </div>
                        ` : ''}
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <p class="font-semibold">NIP:</p>
                                <p>${data.nip || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Nama Lengkap:</p>
                                <p>${data.nama_lengkap}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Email:</p>
                                <p>${data.email || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Kontak:</p>
                                <p>${data.kontak || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Status:</p>
                                <p>${data.status}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Status Aktif:</p>
                                <p>${data.status_aktif}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Tanggal Bergabung:</p>
                                <p>${new Date(data.tanggal_bergabung).toLocaleDateString('id-ID')}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Alamat:</p>
                                <p>${data.alamat || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Mata Pelajaran:</p>
                                <p>${Array.isArray(data.mata_pelajaran) ? 
                                    data.mata_pelajaran.map(m => m.nama).join(', ') || '-' : 
                                    '-'}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#modalTitle').text('Detail Guru');
                $('#modalContent').html(content);
                $('#modalContainer').removeClass('hidden');
            } catch (e) {
                showToast('error', 'Gagal memproses data: ' + e.message);
            }
        },
        error: function(xhr, status, error) {
            showToast('error', 'Gagal memuat detail: ' + error);
        }
    });
});

  // Edit Handler
  $(document).on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    window.location.href = `editGuru.php?id=${id}`;
  });

  // Modal Close Handler
  $(document).on("click", ".modal-close", function () {
    $("#modalContainer").addClass("hidden");
  });

  // Utility Functions
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  function showToast(type, message) {
    $.toast({
      heading: type === "success" ? "Sukses" : "Error",
      text: message,
      icon: type,
      position: "top-right",
      hideAfter: 3000,
    });
  }

  // Initialize
  initializeTable();
});

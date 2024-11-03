// admin/javascript/jurusanScript.js
$(document).ready(function () {
  let currentPage = 1;
  let searchParams = {};
  let refreshInterval;

  function loadData() {
    const limit = parseInt($("#itemsPerPage").val());

    $.ajax({
      url: "../functions/jurusan/",
      type: "POST",
      data: {
        action: "getJurusan",
        page: currentPage,
        limit: limit,
        search: searchParams,
      },
      success: function (response) {
        if (response.status === "success") {
          updateTable(response.data);
          updatePagination(response.total, limit);
          $("#totalData").text(response.total);
        } else {
          showToast("error", response.message || "Terjadi kesalahan");
        }
      },
      error: function (xhr, status, error) {
        showToast("error", "Gagal memuat data: " + error);
      },
    });
  }

  function updateTable(data) {
    const tbody = $("#dataTable tbody");
    tbody.empty();

    if (!data || data.length === 0) {
      tbody.append(`
        <tr>
            <td colspan="5" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
        </tr>
      `);
      return;
    }

    data.forEach((item, index) => {
      const rowNumber =
        (currentPage - 1) * parseInt($("#itemsPerPage").val()) + index + 1;

      const actions =
        $("#userRole").val() === "admin"
          ? `<button class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-1" data-id="${item.id}">
             <i class="fas fa-edit"></i> Edit
           </button>
           <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded" data-id="${item.id}">
             <i class="fas fa-trash"></i> Hapus
           </button>`
          : "";

      tbody.append(`
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">${rowNumber}</td>
            <td class="px-4 py-3">${item.nama_jurusan}</td>
            <td class="px-4 py-3 text-center">${
              item.jumlah_siswa || 0
            } siswa</td>
            <td class="px-4 py-3 text-center">${
              item.jumlah_guru_aktif || 0
            } guru</td>
            <td class="px-4 py-3 text-center">${actions}</td>
        </tr>
      `);
    });
  }

  function updateTable(data) {
    const tbody = $("#dataTable tbody");
    tbody.empty();

    if (!data || data.length === 0) {
      tbody.append(`
        <tr>
            <td colspan="5" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
        </tr>
      `);
      return;
    }

    data.forEach((item, index) => {
      const rowNumber =
        (currentPage - 1) * parseInt($("#itemsPerPage").val()) + index + 1;

      tbody.append(`
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">${rowNumber}</td>
            <td class="px-4 py-3">${item.nama_jurusan}</td>
            <td class="px-4 py-3 text-center">${
              item.jumlah_siswa || 0
            } siswa</td>
            <td class="px-4 py-3 text-center">${
              item.jumlah_guru_aktif || 0
            }</td>
            <td class="px-4 py-3 text-center">
                <button class="edit-btn text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded" data-id="${
                  item.id
                }">
                    Edit
                </button>
                <button class="delete-btn text-white bg-red-500 hover:bg-red-600 px-3 py-1 rounded ml-2" data-id="${
                  item.id
                }">
                    Hapus
                </button>
            </td>
        </tr>
      `);
    });
  }

  // Event Handlers
  $("#searchInput").on(
    "keyup",
    debounce(function () {
      currentPage = 1;
      searchParams.nama = $(this).val();
      loadData();
    }, 500)
  );

  $("#itemsPerPage").on("change", function () {
    currentPage = 1;
    loadData();
  });

  $("#btnTambahJurusan").on("click", function () {
    $("#modalTitle").text("Tambah Jurusan");
    $("#formJurusan")[0].reset();
    $("#jurusanId").val("");
    $('input[name="action"]').val("tambahJurusan");
    $("#modalContainer").removeClass("hidden");
  });

  $(document).on("click", ".edit-btn", function () {
    const id = $(this).data("id");

    $.ajax({
      url: "../functions/jurusan/",
      type: "POST",
      data: {
        action: "getDetailJurusan",
        id: id,
      },
      success: function (response) {
        if (response.status === "success") {
          $("#modalTitle").text("Edit Jurusan");
          $("#jurusanId").val(id);
          $("#namaJurusan").val(response.data.nama_jurusan);
          $('input[name="action"]').val("updateJurusan");
          $("#modalContainer").removeClass("hidden");
        }
      },
    });
  });

  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");

    if (confirm("Apakah Anda yakin ingin menghapus jurusan ini?")) {
      $.ajax({
        url: "../functions/jurusan/",
        type: "POST",
        data: {
          action: "deleteJurusan",
          id: id,
        },
        success: function (response) {
          showToast(response.status, response.message);
          if (response.status === "success") {
            loadData();
          }
        },
      });
    }
  });

  $("#formJurusan").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
      url: "../functions/jurusan/",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        showToast(response.status, response.message);
        if (response.status === "success") {
          $("#modalContainer").addClass("hidden");
          loadData();
        }
      },
    });
  });

  $(".modal-close, #modalContainer").on("click", function (e) {
    if (e.target === this) {
      $("#modalContainer").addClass("hidden");
    }
  });

  // Utilities
  function updatePagination(total, limit) {
    const totalPages = Math.ceil(total / limit);
    const pagination = $(".pagination");
    pagination.empty();

    // Previous button
    pagination.append(`
            <button class="pagination-btn px-3 py-1 rounded border ${
              currentPage === 1
                ? "bg-gray-100 text-gray-400 cursor-not-allowed"
                : "bg-white hover:bg-gray-100"
            }" 
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
                    <button class="pagination-btn px-3 py-1 rounded border ${
                      i === currentPage
                        ? "bg-sky-500 text-white"
                        : "bg-white hover:bg-gray-100"
                    }"
                            data-page="${i}">
                        ${i}
                    </button>
                `);
      } else if (i === currentPage - 3 || i === currentPage + 3) {
        pagination.append('<span class="px-2">...</span>');
      }
    }

    // Next button
    pagination.append(`
            <button class="pagination-btn px-3 py-1 rounded border ${
              currentPage === totalPages
                ? "bg-gray-100 text-gray-400 cursor-not-allowed"
                : "bg-white hover:bg-gray-100"
            }" 
                    data-page="${currentPage + 1}" 
                    ${currentPage === totalPages ? "disabled" : ""}>
                <i class="fas fa-chevron-right"></i>
            </button>
        `);
  }

  function showToast(type, message) {
    $.toast({
      heading: type === "success" ? "Sukses" : "Error",
      text: message,
      icon: type,
      position: "top-right",
      hideAfter: 3000,
      showHideTransition: "slide",
    });
  }

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

  // Handle pagination clicks
  $(document).on("click", ".pagination-btn:not([disabled])", function () {
    currentPage = parseInt($(this).data("page"));
    loadData();
  });

  // Auto refresh setup
  function startAutoRefresh() {
    refreshInterval = setInterval(loadData, 30000); // Refresh every 30 seconds
  }

  function stopAutoRefresh() {
    if (refreshInterval) {
      clearInterval(refreshInterval);
    }
  }

  // Initialize the page
  loadData();
  startAutoRefresh();

  // Cleanup on page leave
  $(window).on("beforeunload", function () {
    stopAutoRefresh();
  });
});

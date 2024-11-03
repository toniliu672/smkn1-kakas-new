// admin/javascript/guruScript.js
$(document).ready(function () {
  let currentPage = 1;
  let itemsPerPage = 10;

  // Fungsi untuk mendapatkan semua filter aktif
  function getActiveFilters() {
    return {
      search: $("#searchInput").val(),
      status: $("select[data-filter='status']").val(),
      status_aktif: $("select[data-filter='status_aktif']").val(),
    };
  }

  // Fungsi utama untuk memuat data
  function loadData() {
    const filters = getActiveFilters();

    $.ajax({
      url: "../functions/guru/",
      type: "POST",
      data: {
        action: "getGuru",
        search: filters.search,
        status: filters.status,
        status_aktif: filters.status_aktif,
        page: currentPage,
        limit: itemsPerPage,
      },
      success: function (response) {
        if (response.status === "success") {
          renderTable(response.data);
          renderPagination(response.total);
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

  // Event handler untuk filter
  $(".filter-select").on("change", function () {
    currentPage = 1; // Reset ke halaman pertama saat filter berubah
    loadData();
  });

  // Event handler untuk pencarian
  $("#searchInput").on(
    "keyup",
    debounce(function () {
      currentPage = 1; // Reset ke halaman pertama saat mencari
      loadData();
    }, 500)
  );

  // Event handler untuk pagination
  $(document).on("click", ".pagination-btn", function () {
    if (!$(this).hasClass("disabled")) {
      currentPage = parseInt($(this).data("page"));
      loadData();
    }
  });

  // Event handler untuk items per page
  $("#itemsPerPage").on("change", function () {
    itemsPerPage = parseInt($(this).val());
    currentPage = 1; // Reset ke halaman pertama
    loadData();
  });

  // Render tabel
  // Render tabel dengan penambahan informasi tracking
  function renderTable(data) {
    const tbody = $("#dataTable tbody");
    tbody.empty();

    if (!data || data.length === 0) {
      tbody.append(`
              <tr>
                  <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                      Tidak ada data ditemukan
                  </td>
              </tr>
          `);
      return;
    }

    data.forEach((item, index) => {
      const rowNumber = (currentPage - 1) * itemsPerPage + index + 1;

      // Format jurusan dengan informasi tracking
      const jurusanDisplay = item.jurusan_aktif
        ? item.jurusan_aktif
            .map(
              (j) => `
              <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-sm mr-1 mb-1" 
                    title="Sejak: ${j.tanggal_mulai}">
                  ${j.nama}
              </span>
          `
            )
            .join("")
        : "-";

      const row = `
              <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3">${rowNumber}</td>
                  <td class="px-4 py-3">${item.nip || "-"}</td>
                  <td class="px-4 py-3">${item.nama_lengkap}</td>
                  <td class="px-4 py-3">${item.kontak || "-"}</td>
                  <td class="px-4 py-3">${item.status}</td>
                  <td class="px-4 py-3">
                      <span class="px-2 py-1 rounded text-sm ${
                        item.status_aktif === "aktif"
                          ? "bg-green-100 text-green-800"
                          : "bg-red-100 text-red-800"
                      }">
                          ${
                            item.status_aktif === "aktif"
                              ? "Aktif"
                              : "Non-aktif"
                          }
                      </span>
                  </td>
                  <td class="px-4 py-3">
                      ${jurusanDisplay}
                  </td>
                  <td class="px-4 py-3 text-center">
                      <a href="detailGuru.php?id=${item.id}" 
                         class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-1">
                          Detail
                      </a>
                      ${
                        $("#userRole").val() === "admin"
                          ? `
                              <a href="editGuru.php?id=${item.id}"
                                 class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm mr-1">
                                  Edit
                              </a>
                              <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm" 
                                      data-id="${item.id}">
                                  Hapus
                              </button>
                          `
                          : ""
                      }
                  </td>
              </tr>
          `;
      tbody.append(row);
    });
  }

  // Render pagination
  function renderPagination(total) {
    const totalPages = Math.ceil(total / itemsPerPage);
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

    // Page numbers
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
  }

  // Utility functions
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

  // Delete handler dengan konfirmasi tambahan
  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");
    const confirmDelete = confirm(
      "Apakah Anda yakin ingin menghapus data ini?\nSemua history tracking juga akan dihapus."
    );

    if (confirmDelete) {
      $.ajax({
        url: "../functions/guru/",
        type: "POST",
        data: {
          action: "deleteGuru",
          id: id,
        },
        success: function (response) {
          if (response.status === "success") {
            showToast("success", response.message);
            loadData();
          } else {
            showToast("error", response.message);
          }
        },
      });
    }
  });

  // Edit handler
  $(document).on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    window.location.href = `editGuru.php?id=${id}`;
  });

  // Initialize
  loadData();
});

// admin/javascript/siswaScript.js
$(document).ready(function () {
  let currentPage = 1;
  let searchParams = {};
  let refreshInterval;

  function startAutoRefresh() {
    refreshInterval = setInterval(loadData, 30000);
  }

  function stopAutoRefresh() {
    clearInterval(refreshInterval);
  }

  function loadData() {
    const limit = parseInt($("#itemsPerPage").val());

    $.ajax({
      url: "../functions/siswa/",
      type: "POST",
      data: {
        action: "getSiswa",
        page: currentPage,
        limit: limit,
        search: searchParams,
      },
      success: function (response) {
        if (response.status === "success") {
          updateTable(response.data);
          updatePagination(response.total, limit);
        } else {
          $.toast({
            heading: "Error",
            text: response.message || "Terjadi kesalahan",
            icon: "error",
            position: "top-right",
          });
        }
      },
      error: function (xhr, status, error) {
        $.toast({
          heading: "Error",
          text: "Gagal memuat data: " + error,
          icon: "error",
          position: "top-right",
        });
      },
    });
  }

  function updateTable(data) {
    const tbody = $("#dataTable tbody");
    tbody.empty();

    if (!data || data.length === 0) {
      tbody.append(`
                <tr>
                    <td colspan="8" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
                </tr>
            `);
      return;
    }

    data.forEach((item, index) => {
      const rowNumber =
        (currentPage - 1) * parseInt($("#itemsPerPage").val()) + index + 1;

      const actions = `
                <a href="detailSiswa.php?id=${item.id}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mr-1">
                    <i class="fas fa-eye"></i> Lihat
                </a>
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-1 edit-btn" 
                        data-id="${item.id}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded delete-btn" 
                        data-id="${item.id}">
                    <i class="fas fa-trash"></i> Hapus
                </button>`;

      tbody.append(`
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">${rowNumber}</td>
                    <td class="px-4 py-3">${item.nis || "-"}</td>
                    <td class="px-4 py-3">${item.nisn || "-"}</td>
                    <td class="px-4 py-3">${item.nama_lengkap}</td>
                    <td class="px-4 py-3">${item.angkatan}</td>
                    <td class="px-4 py-3">${item.nama_jurusan}</td>
                    <td class="px-4 py-3">${item.no_hp || "-"}</td>
                    <td class="px-4 py-3 text-center">${actions}</td>
                </tr>
            `);
    });
  }

  function updatePagination(total, limit) {
    const totalPages = Math.ceil(total / limit);
    const pagination = $(".pagination");
    pagination.empty();

    // Previous button
    pagination.append(`
            <button class="pagination-btn px-3 py-1 rounded border ${
              currentPage === 1
                ? "text-gray-400 cursor-not-allowed"
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
                        ? "bg-blue-500 text-white"
                        : "bg-white hover:bg-gray-100"
                    }"
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
            <button class="pagination-btn px-3 py-1 rounded border ${
              currentPage === totalPages
                ? "text-gray-400 cursor-not-allowed"
                : "bg-white hover:bg-gray-100"
            }" 
                    data-page="${currentPage + 1}" 
                    ${currentPage === totalPages ? "disabled" : ""}>
                <i class="fas fa-chevron-right"></i>
            </button>
        `);

    $("#totalData").text(total);
  }

  function getPrintUrl() {
    let url = "./printSiswa.php";
    let params = [];

    if (searchParams.angkatan) {
      params.push(`angkatan=${searchParams.angkatan}`);
    }
    if (searchParams.jurusan) {
      params.push(`jurusan=${searchParams.jurusan}`);
    }

    return url + (params.length ? "?" + params.join("&") : "");
  }

  // Update link cetak saat filter berubah
  function updatePrintLink() {
    $(".print-link").attr("href", getPrintUrl());
  }

  // Search dan filter handlers
  $("#searchInput").on("keyup", function () {
    currentPage = 1;
    searchParams.nama = $(this).val();
    loadData();
  });

  $(".filter-select").on("change", function () {
    currentPage = 1;
    searchParams[$(this).data("filter")] = $(this).val();
    loadData();
    updatePrintLink();
  });

  // Edit handler
  $(document).on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    window.location.href = `editSiswa.php?id=${id}`;
  });

  // Delete handler
  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");

    if (confirm("Apakah Anda yakin ingin menghapus data siswa ini?")) {
      $.ajax({
        url: "../functions/siswa/",
        type: "POST",
        data: {
          action: "deleteSiswa",
          id: id,
        },
        success: function (response) {
          if (response.status === "success") {
            $.toast({
              heading: "Success",
              text: response.message,
              icon: "success",
              position: "top-right",
            });
            loadData();
          } else {
            $.toast({
              heading: "Error",
              text: response.message,
              icon: "error",
              position: "top-right",
            });
          }
        },
      });
    }
  });

  // Initialize
  loadData();
  startAutoRefresh();

  // Cleanup
  $(window).on("beforeunload", function () {
    stopAutoRefresh();
  });
});

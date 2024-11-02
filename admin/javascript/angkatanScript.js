// admin/javascript/angkatanScript.js
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
      url: "../functions/angkatan/",
      type: "POST",
      data: {
        action: "getAngkatan",
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
                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
                </tr>
            `);
      return;
    }

    data.forEach((item, index) => {
      const rowNumber =
        (currentPage - 1) * parseInt($("#itemsPerPage").val()) + index + 1;

      const actions = `
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
                    <td class="px-4 py-3">${item.tahun}</td>
                    <td class="px-4 py-3 text-center">${item.jumlah_siswa} siswa</td>
                    <td class="px-4 py-3 text-center">${actions}</td>
                </tr>
            `);
    });
  }

  function updatePagination(total, limit) {
    const totalPages = Math.ceil(total / limit);
    const pagination = $(".pagination");
    pagination.empty();

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

  $("#searchInput").on("keyup", function () {
    currentPage = 1;
    searchParams.tahun = $(this).val();
    loadData();
  });

  $("#itemsPerPage").on("change", function () {
    currentPage = 1;
    loadData();
  });

  $("#btnTambahAngkatan").on("click", function () {
    stopAutoRefresh();
    $("#modalTitle").text("Tambah Angkatan");
    $("#formAngkatan")[0].reset();
    $('#formAngkatan input[name="action"]').val("tambahAngkatan");
    $('#formAngkatan input[name="id"]').val("");
    $("#modalContainer").removeClass("hidden");
  });

  $(document).on("click", ".view-btn", function () {
    stopAutoRefresh();
    const id = $(this).data("id");

    $.ajax({
      url: "../functions/angkatan/",
      type: "POST",
      data: {
        action: "getDetailAngkatan",
        id: id,
      },
      success: function (response) {
        if (response.status === "success") {
          const angkatan = response.data;
          const content = `
                        <div class="p-4">
                            <div class="mb-4">
                                <h3 class="font-bold text-lg">Informasi Angkatan</h3>
                                <p class="text-gray-600">Tahun: ${
                                  angkatan.tahun
                                }</p>
                                <p class="text-gray-600">Total Siswa: ${
                                  angkatan.jumlah_siswa
                                }</p>
                            </div>
                            ${
                              angkatan.jurusan
                                ? `
                                <div class="mb-4">
                                    <h3 class="font-bold text-lg">Distribusi per Jurusan</h3>
                                    <ul class="list-disc list-inside">
                                        ${angkatan.jurusan
                                          .map(
                                            (j) => `
                                            <li class="text-gray-600">${j.nama_jurusan}: ${j.jumlah} siswa</li>
                                        `
                                          )
                                          .join("")}
                                    </ul>
                                </div>
                            `
                                : ""
                            }
                        </div>
                    `;

          $("#modalTitle").text("Detail Angkatan");
          $("#modalContent").html(content);
          $("#modalContainer").removeClass("hidden");
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
  });

  $(document).on("click", ".edit-btn", function () {
    stopAutoRefresh();
    const id = $(this).data("id");

    $.ajax({
      url: "../functions/angkatan/",
      type: "POST",
      data: {
        action: "getDetailAngkatan",
        id: id,
      },
      success: function (response) {
        if (response.status === "success") {
          $("#modalTitle").text("Edit Angkatan");
          $('#formAngkatan input[name="action"]').val("updateAngkatan");
          $('#formAngkatan input[name="id"]').val(id);
          $('#formAngkatan input[name="tahun"]').val(response.data.tahun);
          $("#modalContainer").removeClass("hidden");
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
  });

  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");

    if (
      confirm(
        "Apakah Anda yakin ingin menghapus angkatan ini?\nSemua data terkait akan terhapus."
      )
    ) {
      $.ajax({
        url: "../functions/angkatan/",
        type: "POST",
        data: {
          action: "deleteAngkatan",
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

  $("#formAngkatan").on("submit", function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: "../functions/angkatan/",
      type: "POST",
      data: formData,
      success: function (response) {
        if (response.status === "success") {
          $.toast({
            heading: "Success",
            text: response.message,
            icon: "success",
            position: "top-right",
          });
          $("#modalContainer").addClass("hidden");
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
  });

  $(".modal-close, #modalContainer").on("click", function (e) {
    if (e.target === this) {
      $("#modalContainer").addClass("hidden");
      startAutoRefresh();
    }
  });

  loadData();
  startAutoRefresh();

  $(window).on("beforeunload", function () {
    stopAutoRefresh();
  });
});

// admin/javascript/mapelScript.js
$(document).ready(function() {
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
        const limit = parseInt($('#itemsPerPage').val());
        
        $.ajax({
            url: '../functions/mapel/',
            type: 'POST',
            data: {
                action: 'getMapel',
                page: currentPage,
                limit: limit,
                search: searchParams
            },
            success: function(response) {
                if (response.status === 'success') {
                    updateTable(response.data);
                    updatePagination(response.total, limit);
                } else {
                    showToast('error', response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Gagal memuat data: ' + error);
            }
        });
    }

    function updateTable(data) {
        const tbody = $('#dataTable tbody');
        tbody.empty();
        
        if (!data || data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td>
                </tr>
            `);
            return;
        }
        
        data.forEach((item, index) => {
            const rowNumber = (currentPage - 1) * parseInt($('#itemsPerPage').val()) + index + 1;
            
            const actions = `
                <button class="px-3 py-1 bg-blue-500 text-white rounded mr-1 view-btn" data-id="${item.id}">
                    Lihat
                </button>
                <button class="px-3 py-1 bg-yellow-500 text-white rounded mr-1 edit-btn" data-id="${item.id}">
                    Edit
                </button>
                <button class="px-3 py-1 bg-red-500 text-white rounded delete-btn" data-id="${item.id}">
                    Hapus
                </button>`;

            tbody.append(`
                <tr>
                    <td class="px-4 py-3">${rowNumber}</td>
                    <td class="px-4 py-3">${item.kode_mapel || '-'}</td>
                    <td class="px-4 py-3">${item.nama_mata_pelajaran}</td>
                    <td class="px-4 py-3">${item.kategori}</td>
                    <td class="px-4 py-3">${item.tingkat?.replace(/,/g, ', ') || '-'}</td>
                    <td class="px-4 py-3 text-center">${actions}</td>
                </tr>
            `);
        });
    }

    function updatePagination(total, limit) {
        const totalPages = Math.ceil(total / limit);
        const pagination = $('.pagination');
        pagination.empty();

        // Previous button
        pagination.append(`
            <button class="pagination-btn px-3 py-1 bg-white rounded border ${currentPage === 1 ? 'text-gray-400' : ''}" 
                    data-page="${currentPage - 1}" 
                    ${currentPage === 1 ? 'disabled' : ''}>
                Prev
            </button>
        `);

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                pagination.append(`
                    <button class="pagination-btn px-3 py-1 rounded border ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-white'}"
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
            <button class="pagination-btn px-3 py-1 bg-white rounded border ${currentPage === totalPages ? 'text-gray-400' : ''}" 
                    data-page="${currentPage + 1}" 
                    ${currentPage === totalPages ? 'disabled' : ''}>
                Next
            </button>
        `);

        $('#totalData').text(total);
    }

    // Pagination click handler
    $(document).on('click', '.pagination-btn:not([disabled])', function() {
        currentPage = parseInt($(this).data('page'));
        loadData();
    });

    // Search and filter handlers
    $('#searchInput, .filter-select').on('change keyup', function() {
        currentPage = 1;
        searchParams = {
            nama: $('#searchInput').val(),
            kategori: $('.filter-select[data-filter="kategori"]').val(),
            tingkat: $('.filter-select[data-filter="tingkat"]').val()
        };
        loadData();
    });

    $('#itemsPerPage').on('change', function() {
        currentPage = 1;
        loadData();
    });

    // View handler
    $(document).on('click', '.view-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        $.ajax({
            url: '../functions/mapel/',
            type: 'POST',
            data: {
                action: 'getDetailMapel',
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    const content = `
                        <div class="grid grid-cols-2 gap-4 p-4">
                            <div>
                                <p class="font-semibold">Kode:</p>
                                <p>${response.data.kode_mapel || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Nama:</p>
                                <p>${response.data.nama_mata_pelajaran}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Kategori:</p>
                                <p>${response.data.kategori}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Tingkat:</p>
                                <p>${response.data.tingkat_list?.replace(/,/g, ', ') || '-'}</p>
                            </div>
                        </div>
                    `;
                    
                    $('#modalTitle').text('Detail Mata Pelajaran');
                    $('#formMapel').addClass('hidden');
                    $('#detailView').html(content).removeClass('hidden');
                    $('#modalContainer').removeClass('hidden');
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Gagal memuat detail: ' + error);
                startAutoRefresh();
            }
        });
    });

    // Edit handler
    $(document).on('click', '.edit-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        $.ajax({
            url: '../functions/mapel/',
            type: 'POST',
            data: {
                action: 'getDetailMapel',
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    const mapel = response.data;
                    $('#modalTitle').text('Edit Mata Pelajaran');
                    $('#formMapel input[name="action"]').val('updateMapel');
                    $('#formMapel input[name="id"]').val(mapel.id);
                    $('#formMapel input[name="kode_mapel"]').val(mapel.kode_mapel || '');
                    $('#formMapel input[name="nama_mata_pelajaran"]').val(mapel.nama_mata_pelajaran);
                    $('#formMapel select[name="kategori"]').val(mapel.kategori);
                    
                    $('input[name="tingkat[]"]').prop('checked', false);
                    if (mapel.tingkat_list) {
                        const tingkatList = mapel.tingkat_list.split(',');
                        tingkatList.forEach(tingkat => {
                            $(`input[name="tingkat[]"][value="${tingkat.trim()}"]`).prop('checked', true);
                        });
                    }
                    
                    $('#detailView').addClass('hidden');
                    $('#formMapel').removeClass('hidden');
                    $('#modalContainer').removeClass('hidden');
                } else {
                    showToast('error', response.message || 'Gagal memuat data');
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Gagal memuat data: ' + error);
            }
        });
    });

    // Delete handler
    $(document).on('click', '.delete-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?')) {
            $.ajax({
                url: '../functions/mapel/',
                type: 'POST',
                data: {
                    action: 'deleteMapel',
                    id: id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showToast('success', response.message);
                        loadData();
                    } else {
                        showToast('error', response.message);
                    }
                    startAutoRefresh();
                },
                error: function(xhr, status, error) {
                    showToast('error', 'Gagal menghapus data: ' + error);
                    startAutoRefresh();
                }
            });
        } else {
            startAutoRefresh();
        }
    });

    // Form submit handler
    $('#formMapel').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: '../functions/mapel/',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    showToast('success', response.message);
                    $('#modalContainer').addClass('hidden');
                    loadData();
                } else {
                    showToast('error', response.message || 'Gagal menyimpan data');
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Gagal menyimpan data: ' + error);
            }
        });
    });

    // Modal close handlers
    $('.modal-close, #modalContainer').on('click', function(e) {
        if (e.target === this) {
            $('#modalContainer').addClass('hidden');
            startAutoRefresh();
        }
    });

    $('#btnTambahMapel').click(function() {
        stopAutoRefresh();
        $('#modalTitle').text('Tambah Mata Pelajaran');
        $('#formMapel')[0].reset();
        $('#formMapel input[name="action"]').val('tambahMapel');
        $('#formMapel input[name="id"]').val('');
        $('#detailView').addClass('hidden');
        $('#formMapel').removeClass('hidden');
        $('#modalContainer').removeClass('hidden');
    });

    function showToast(type, message) {
        $.toast({
            heading: type === 'success' ? 'Sukses' : 'Error',
            text: message,
            icon: type,
            position: 'top-right',
            hideAfter: 3000
        });
    }

    // Initialize dengan auto refresh
    loadData();
    startAutoRefresh();

    // Cleanup saat user meninggalkan halaman
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
});
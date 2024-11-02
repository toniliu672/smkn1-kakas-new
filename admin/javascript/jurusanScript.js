// admin/javascript/jurusanScript.js

$(document).ready(function() {
    let currentPage = 1;
    let searchParams = {};
    let refreshInterval;

    // Fungsi untuk memulai auto refresh
    function startAutoRefresh() {
        refreshInterval = setInterval(loadData, 30000); // Refresh setiap 30 detik
    }

    // Fungsi untuk menghentikan auto refresh
    function stopAutoRefresh() {
        clearInterval(refreshInterval);
    }

    // Fungsi untuk memuat data
    function loadData() {
        const limit = parseInt($('#itemsPerPage').val());
        
        $.ajax({
            url: '../functions/jurusan/',
            type: 'POST',
            data: {
                action: 'getJurusan',
                page: currentPage,
                limit: limit,
                search: searchParams
            },
            success: function(response) {
                if (response.status === 'success') {
                    updateTable(response.data);
                    updatePagination(response.total, limit);
                } else {
                    $.toast({
                        heading: 'Error',
                        text: response.message || 'Terjadi kesalahan',
                        icon: 'error',
                        position: 'top-right'
                    });
                }
            },
            error: function(xhr, status, error) {
                $.toast({
                    heading: 'Error',
                    text: 'Gagal memuat data: ' + error,
                    icon: 'error',
                    position: 'top-right'
                });
            }
        });
    }

    // Fungsi untuk update tabel
    function updateTable(data) {
        const tbody = $('#dataTable tbody');
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
            const rowNumber = (currentPage - 1) * parseInt($('#itemsPerPage').val()) + index + 1;
            
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
                    <td class="px-4 py-3">${item.nama_jurusan}</td>
                    <td class="px-4 py-3 text-center">${item.jumlah_siswa_formatted} siswa</td>
                    <td class="px-4 py-3 text-center">${actions}</td>
                </tr>
            `);
        });
    }

    // Fungsi untuk update pagination
    function updatePagination(total, limit) {
        const totalPages = Math.ceil(total / limit);
        const pagination = $('.pagination');
        pagination.empty();

        // Previous button
        pagination.append(`
            <button class="pagination-btn px-3 py-1 rounded border ${currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'}" 
                    data-page="${currentPage - 1}" 
                    ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        `);

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                pagination.append(`
                    <button class="pagination-btn px-3 py-1 rounded border ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100'}"
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
            <button class="pagination-btn px-3 py-1 rounded border ${currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'}" 
                    data-page="${currentPage + 1}" 
                    ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        `);

        $('#totalData').text(total);
    }

    // Event handler untuk pagination
    $(document).on('click', '.pagination-btn:not([disabled])', function() {
        currentPage = parseInt($(this).data('page'));
        loadData();
    });

    // Event handler untuk pencarian
    $('#searchInput').on('keyup', function() {
        currentPage = 1;
        searchParams.nama = $(this).val();
        loadData();
    });

    // Event handler untuk items per page
    $('#itemsPerPage').on('change', function() {
        currentPage = 1;
        loadData();
    });

    // Event handler untuk tambah jurusan
    $('#btnTambahJurusan').on('click', function() {
        stopAutoRefresh();
        $('#modalTitle').text('Tambah Jurusan');
        $('#formJurusan')[0].reset();
        $('#formJurusan input[name="action"]').val('tambahJurusan');
        $('#formJurusan input[name="id"]').val('');
        $('#modalContainer').removeClass('hidden');
    });

    // Event handler untuk view jurusan
    $(document).on('click', '.view-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        $.ajax({
            url: '../functions/jurusan/',
            type: 'POST',
            data: {
                action: 'getDetailJurusan',
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    const jurusan = response.data;
                    const content = `
                        <div class="p-4">
                            <div class="mb-4">
                                <h3 class="font-bold text-lg">Informasi Jurusan</h3>
                                <p class="text-gray-600">Nama: ${jurusan.nama_jurusan}</p>
                                <p class="text-gray-600">Total Siswa: ${jurusan.total_siswa}</p>
                            </div>
                            <div class="mb-4">
                                <h3 class="font-bold text-lg">Tahun Angkatan</h3>
                                <p class="text-gray-600">${jurusan.tahun_angkatan.join(', ') || 'Tidak ada data'}</p>
                            </div>
                        </div>
                    `;
                    
                    $('#modalTitle').text('Detail Jurusan');
                    $('#modalContent').html(content);
                    $('#modalContainer').removeClass('hidden');
                } else {
                    $.toast({
                        heading: 'Error',
                        text: response.message,
                        icon: 'error',
                        position: 'top-right'
                    });
                }
            },
            error: function(xhr, status, error) {
                $.toast({
                    heading: 'Error',
                    text: 'Gagal memuat detail: ' + error,
                    icon: 'error',
                    position: 'top-right'
                });
            }
        });
    });

    // Event handler untuk edit jurusan
    $(document).on('click', '.edit-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        $.ajax({
            url: '../functions/jurusan/',
            type: 'POST',
            data: {
                action: 'getDetailJurusan',
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalTitle').text('Edit Jurusan');
                    $('#formJurusan input[name="action"]').val('updateJurusan');
                    $('#formJurusan input[name="id"]').val(id);
                    $('#formJurusan input[name="nama_jurusan"]').val(response.data.nama_jurusan);
                    $('#modalContainer').removeClass('hidden');
                } else {
                    $.toast({
                        heading: 'Error',
                        text: response.message,
                        icon: 'error',
                        position: 'top-right'
                    });
                }
            }
        });
    });

    // Event handler untuk delete jurusan
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus jurusan ini?')) {
            $.ajax({
                url: '../functions/jurusan/',
                type: 'POST',
                data: {
                    action: 'deleteJurusan',
                    id: id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            icon: 'success',
                            position: 'top-right'
                        });
                        loadData();
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: response.message,
                            icon: 'error',
                            position: 'top-right'
                        });
                    }
                }
            });
        }
    });

    // Event handler untuk submit form
    $('#formJurusan').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: '../functions/jurusan/',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $.toast({
                        heading: 'Success',
                        text: response.message,
                        icon: 'success',
                        position: 'top-right'
                    });
                    $('#modalContainer').addClass('hidden');
                    loadData();
                } else {
                    $.toast({
                        heading: 'Error',
                        text: response.message,
                        icon: 'error',
                        position: 'top-right'
                    });
                }
            }
        });
    });

    // Event handler untuk close modal
    $('.modal-close, #modalContainer').on('click', function(e) {
        if (e.target === this) {
            $('#modalContainer').addClass('hidden');
            startAutoRefresh();
        }
    });

    // Initialize
    loadData();
    startAutoRefresh();

    // Cleanup saat user meninggalkan halaman
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
});
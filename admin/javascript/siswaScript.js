// admin/javascript/siswaScript.js
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
            url: '../functions/siswa/',
            type: 'POST',
            data: {
                action: 'getSiswa',
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

    function updateTable(data) {
        const tbody = $('#dataTable tbody');
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
            const rowNumber = (currentPage - 1) * parseInt($('#itemsPerPage').val()) + index + 1;
            
            const actions = `
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mr-1 view-btn" 
                        data-id="${item.id}">
                    <i class="fas fa-eye"></i> Lihat
                </button>
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
                    <td class="px-4 py-3">${item.nis || '-'}</td>
                    <td class="px-4 py-3">${item.nisn || '-'}</td>
                    <td class="px-4 py-3">${item.nama_lengkap}</td>
                    <td class="px-4 py-3">${item.angkatan}</td>
                    <td class="px-4 py-3">${item.nama_jurusan}</td>
                    <td class="px-4 py-3">${item.no_hp || '-'}</td>
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

    function getPrintUrl() {
        let url = './printSiswa.php';
        let params = [];
        
        if (searchParams.angkatan) {
            params.push(`angkatan=${searchParams.angkatan}`);
        }
        if (searchParams.jurusan) {
            params.push(`jurusan=${searchParams.jurusan}`);
        }
        
        return url + (params.length ? '?' + params.join('&') : '');
    }
    
    // Update link cetak saat filter berubah
    function updatePrintLink() {
        $('.print-link').attr('href', getPrintUrl());
    }

    // Search dan filter handlers
    $('#searchInput').on('keyup', function() {
        currentPage = 1;
        searchParams.nama = $(this).val();
        loadData();
    });

    $('.filter-select').on('change', function() {
        currentPage = 1;
        searchParams[$(this).data('filter')] = $(this).val();
        loadData();
        updatePrintLink();
    });

    // View handler
    $(document).on('click', '.view-btn', function() {
        stopAutoRefresh();
        const id = $(this).data('id');
        
        $.ajax({
            url: '../functions/siswa/',
            type: 'POST',
            data: {
                action: 'getDetailSiswa',
                id: id
            },
            success: function(response) {
                if (response.status === 'success') {
                    const siswa = response.data;
                    const content = `
                    <div class="p-4">
                        ${siswa.foto_siswa ? `
                            <div class="mb-4">
                                <img src="../../${siswa.foto_siswa}" alt="Foto Siswa" 
                                     class="w-32 h-32 object-cover rounded">
                            </div>
                        ` : ''}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">NIS:</p>
                                <p>${siswa.nis || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">NISN:</p>
                                <p>${siswa.nisn || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Nama Lengkap:</p>
                                <p>${siswa.nama_lengkap}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Tempat Lahir:</p>
                                <p>${siswa.tempat_lahir || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Tanggal Lahir:</p>
                                <p>${siswa.tanggal_lahir ? new Date(siswa.tanggal_lahir).toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                }) : '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Jenis Kelamin:</p>
                                <p>${siswa.jenis_kelamin || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Agama:</p>
                                <p>${siswa.agama || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Angkatan:</p>
                                <p>${siswa.angkatan}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Jurusan:</p>
                                <p>${siswa.nama_jurusan}</p>
                            </div>
                            <div>
                                <p class="font-semibold">No. HP:</p>
                                <p>${siswa.no_hp || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Email:</p>
                                <p>${siswa.email || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Alamat:</p>
                                <p>${siswa.alamat || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Nama Ayah:</p>
                                <p>${siswa.nama_ayah || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Nama Ibu:</p>
                                <p>${siswa.nama_ibu || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Pekerjaan Ayah:</p>
                                <p>${siswa.pekerjaan_ayah || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Pekerjaan Ibu:</p>
                                <p>${siswa.pekerjaan_ibu || '-'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">No. HP Orang Tua:</p>
                                <p>${siswa.no_hp_orang_tua || '-'}</p>
                            </div>
                        </div>
                    </div>
                `;
                    
                    $('#modalTitle').text('Detail Siswa');
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
            }
        });
    });

    // Edit handler
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        window.location.href = `editSiswa.php?id=${id}`;
    });

    // Delete handler
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus data siswa ini?')) {
            $.ajax({
                url: '../functions/siswa/',
                type: 'POST',
                data: {
                    action: 'deleteSiswa',
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

    // Modal close handler
    $('.modal-close, #modalContainer').on('click', function(e) {
        if (e.target === this) {
            $('#modalContainer').addClass('hidden');
            startAutoRefresh();
        }
    });

    // Initialize
    loadData();
    startAutoRefresh();

    // Cleanup
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
});
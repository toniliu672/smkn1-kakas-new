// admin/javascript/chartGuru.js
const GuruChart = {
    chart: null,
    // Definisi warna yang berbeda untuk setiap status
    statusColors: {
        'PNS': '#2563eb',      // Biru
        'Honorer': '#16a34a',  // Hijau
        'Kontrak': '#dc2626'   // Merah
    },
    // Warna untuk keaktifan
    keaktifanColors: {
        'aktif': '#16a34a',    // Hijau
        'non-aktif': '#dc2626' // Merah
    },
    // Warna untuk mata pelajaran
    mapelColors: '#3b82f6',    // Biru

    init(chartId, summaryData) {
        const selectElement = document.getElementById(chartId + 'Type');
        if (selectElement) {
            selectElement.addEventListener('change', (e) => this.updateChart(chartId, e.target.value, summaryData));
            this.updateChart(chartId, 'status', summaryData);
        }
    },

    async updateChart(chartId, type, summaryData) {
        try {
            const response = await fetch(`../functions/chart/get${this.capitalize(type)}ChartData.php`);
            const result = await response.json();

            if (!result?.status || !Array.isArray(result.data)) {
                console.error('Invalid data format received:', result);
                return;
            }

            if (this.chart) {
                this.chart.destroy();
            }

            const ctx = document.getElementById(chartId);
            if (ctx) {
                const config = this.generateChartConfig(type, result.data, summaryData);
                this.chart = new Chart(ctx, config);
            }
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    },

    generateChartConfig(type, data, summaryData) {
        switch(type) {
            case 'status':
                return {
                    type: 'pie',
                    data: {
                        labels: data.map(item => item.status),
                        datasets: [{
                            data: data.map(item => parseInt(item.jumlah)),
                            backgroundColor: data.map(item => this.statusColors[item.status])
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' },
                            title: { 
                                display: true, 
                                text: 'Status Kepegawaian Guru',
                                font: { size: 16 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = data[context.dataIndex];
                                        return `${item.status}: ${item.jumlah} (${item.persentase}%)`;
                                    }
                                }
                            }
                        }
                    }
                };

            case 'keaktifan':
                return {
                    type: 'pie',
                    data: {
                        labels: data.map(item => item.status_aktif === 'aktif' ? 'Aktif' : 'Non-Aktif'),
                        datasets: [{
                            data: data.map(item => parseInt(item.jumlah)),
                            backgroundColor: data.map(item => this.keaktifanColors[item.status_aktif])
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            title: { 
                                display: true, 
                                text: 'Status Keaktifan Guru',
                                font: { size: 16 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = data[context.dataIndex];
                                        return `${item.status_aktif}: ${item.jumlah} (${item.persentase}%)`;
                                    }
                                }
                            }
                        }
                    }
                };

            case 'mapel':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.nama_mata_pelajaran),
                        datasets: [{
                            label: 'Jumlah Guru',
                            data: data.map(item => parseInt(item.jumlah_guru)),
                            backgroundColor: this.mapelColors
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            title: { 
                                display: true, 
                                text: 'Jumlah Guru per Mata Pelajaran',
                                font: { size: 16 }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                };
        }
    },

    capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
};

window.GuruChart = GuruChart;
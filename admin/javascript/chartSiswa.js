// admin/javascript/chartSiswa.js
const SiswaChart = {
    chart: null,
    chartColors: [
        '#2563eb', '#16a34a', '#dc2626', '#ca8a04', 
        '#7c3aed', '#0891b2', '#db2777', '#84cc16'
    ],

    init(chartId, summaryData) {
        const selectElement = document.getElementById(chartId + 'Type');
        if (selectElement) {
            selectElement.addEventListener('change', (e) => this.updateChart(chartId, e.target.value, summaryData));
            this.updateChart(chartId, 'gender', summaryData);
        }
    },

    async updateChart(chartId, type, summaryData) {
        try {
            const response = await fetch(`../functions/chart/get${this.capitalize(type)}ChartData.php`);
            const result = await response.json();
            
            if (!result || !result.data) {
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
            case 'gender':
                return {
                    type: 'pie',
                    data: {
                        labels: ['Laki-laki', 'Perempuan'],
                        datasets: [{
                            data: [summaryData.laki_laki, summaryData.perempuan],
                            backgroundColor: ['#2563eb', '#db2777']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: 'Distribusi Jenis Kelamin' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = summaryData.total;
                                        const percentage = ((value/total) * 100).toFixed(1);
                                        return `${context.label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                };

            case 'jurusan':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.nama_jurusan),
                        datasets: [{
                            label: 'Jumlah Siswa',
                            data: data.map(item => parseInt(item.jumlah)),
                            backgroundColor: '#2563eb'
                        }]
                    },
                    options: this.getDefaultBarOptions('Jumlah Siswa per Jurusan')
                };

            case 'angkatan':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.angkatan),
                        datasets: [
                            {
                                label: 'Laki-laki',
                                data: data.map(item => parseInt(item.laki_laki)),
                                backgroundColor: '#2563eb'
                            },
                            {
                                label: 'Perempuan',
                                data: data.map(item => parseInt(item.perempuan)),
                                backgroundColor: '#db2777'
                            }
                        ]
                    },
                    options: this.getDefaultBarOptions('Jumlah Siswa per Angkatan', true)
                };

            case 'agama':
                return {
                    type: 'doughnut',
                    data: {
                        labels: data.map(item => item.agama),
                        datasets: [{
                            data: data.map(item => parseInt(item.jumlah)),
                            backgroundColor: this.chartColors
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' },
                            title: { display: true, text: 'Distribusi Agama Siswa' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = data[context.dataIndex];
                                        return `${item.agama}: ${item.jumlah} (${item.persentase}%)`;
                                    }
                                }
                            }
                        }
                    }
                };
        }
    },

    getDefaultBarOptions(title, stacked = false) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: title }
            },
            scales: {
                x: { stacked },
                y: {
                    stacked,
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        };
    },

    capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
};

window.SiswaChart = SiswaChart;
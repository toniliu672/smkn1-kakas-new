<?php
// admin/components/charts.php

function getSummaryData($pdo, $type)
{
    if ($type === 'siswa') {
        $query = "SELECT 
            (SELECT COUNT(*) FROM siswa) as total,
            (SELECT COUNT(*) FROM siswa WHERE jenis_kelamin = 'Laki-laki') as laki_laki,
            (SELECT COUNT(*) FROM siswa WHERE jenis_kelamin = 'Perempuan') as perempuan";
    } else {
        $query = "SELECT 
            (SELECT COUNT(*) FROM guru) as total,
            (SELECT COUNT(*) FROM guru WHERE status_aktif = 'aktif') as aktif,
            (SELECT COUNT(*) FROM guru WHERE status_aktif = 'non-aktif') as non_aktif";
    }

    $stmt = $pdo->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createChartComponent($pdo, $chartId, $title, $type = 'bar')
{
    $summaryData = getSummaryData($pdo, $type);
    ob_start();
?>
    <div class="glassmorphism mb-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <?php if ($type === 'siswa'): ?>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-blue-700 font-semibold">Total Siswa</h4>
                    <p class="text-2xl font-bold text-blue-800"><?php echo number_format($summaryData['total']); ?></p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-blue-700 font-semibold">Laki-laki</h4>
                    <p class="text-2xl font-bold text-blue-800"><?php echo number_format($summaryData['laki_laki']); ?></p>
                    <p class="text-sm text-blue-600"><?php echo round(($summaryData['laki_laki'] / $summaryData['total']) * 100, 1); ?>%</p>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg">
                    <h4 class="text-pink-700 font-semibold">Perempuan</h4>
                    <p class="text-2xl font-bold text-pink-800"><?php echo number_format($summaryData['perempuan']); ?></p>
                    <p class="text-sm text-pink-600"><?php echo round(($summaryData['perempuan'] / $summaryData['total']) * 100, 1); ?>%</p>
                </div>
            <?php else: ?>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-blue-700 font-semibold">Total Guru</h4>
                    <p class="text-2xl font-bold text-blue-800"><?php echo number_format($summaryData['total']); ?></p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-green-700 font-semibold">Aktif</h4>
                    <p class="text-2xl font-bold text-green-800"><?php echo number_format($summaryData['aktif']); ?></p>
                    <p class="text-sm text-green-600"><?php echo round(($summaryData['aktif'] / $summaryData['total']) * 100, 1); ?>%</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h4 class="text-red-700 font-semibold">Non-Aktif</h4>
                    <p class="text-2xl font-bold text-red-800"><?php echo number_format($summaryData['non_aktif']); ?></p>
                    <p class="text-sm text-red-600"><?php echo round(($summaryData['non_aktif'] / $summaryData['total']) * 100, 1); ?>%</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chart Controls -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($title); ?></h3>
            <?php if ($type === 'siswa'): ?>
                <select id="<?php echo $chartId; ?>Type" class="bg-white border border-gray-300 rounded px-3 py-1">
                    <option value="gender">Jenis Kelamin</option>
                    <option value="jurusan">Per Jurusan</option>
                    <option value="angkatan">Per Angkatan</option>
                    <option value="agama">Per Agama</option>
                </select>
            <?php else: ?>
                <select id="<?php echo $chartId; ?>Type" class="bg-white border border-gray-300 rounded px-3 py-1">
                    <option value="status">Status Kepegawaian</option>
                    <option value="keaktifan">Status Keaktifan</option>
                    <option value="mapel">Per Mata Pelajaran</option>
                </select>
            <?php endif; ?>
        </div>

        <!-- Chart Container -->
        <div class="relative" style="height: 400px;">
            <canvas id="<?php echo $chartId; ?>"></canvas>
        </div>
    </div>

    <?php if ($type === 'siswa'): ?>
        <script src="../javascript/chartSiswa.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                SiswaChart.init('<?php echo $chartId; ?>', <?php echo json_encode($summaryData); ?>);
            });
        </script>
    <?php else: ?>
        <script src="../javascript/chartGuru.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                GuruChart.init('<?php echo $chartId; ?>', <?php echo json_encode($summaryData); ?>);
            });
        </script>
    <?php endif; ?>

<?php
    return ob_get_clean();
}
?>
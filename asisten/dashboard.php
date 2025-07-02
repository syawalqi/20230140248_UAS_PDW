<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once '../config.php';
require_once 'templates/header.php';

$asisten_id = $_SESSION['user_id'];

// 1. Ambil semua praktikum yang diasistenkan
$praktikumQuery = $conn->prepare("SELECT id FROM praktikum WHERE asisten_id = ?");
$praktikumQuery->bind_param("i", $asisten_id);
$praktikumQuery->execute();
$praktikumResult = $praktikumQuery->get_result();

$praktikum_ids = [];
while ($row = $praktikumResult->fetch_assoc()) {
    $praktikum_ids[] = $row['id'];
}

if (empty($praktikum_ids)) {
    $praktikum_ids = [0]; // biar query IN tidak error
}
$idList = implode(',', $praktikum_ids);

// 2. Total Modul Diajarkan
$modulCount = $conn->query("SELECT COUNT(*) AS total FROM modul WHERE praktikum_id IN ($idList)")->fetch_assoc()['total'];

// 3. Total Laporan Masuk
$laporanCount = $conn->query("
    SELECT COUNT(*) AS total 
    FROM laporan 
    WHERE modul_id IN (
        SELECT id FROM modul WHERE praktikum_id IN ($idList)
    )
")->fetch_assoc()['total'];

// 4. Laporan Belum Dinilai
$belumDinilai = $conn->query("
    SELECT COUNT(*) AS total 
    FROM laporan 
    WHERE nilai IS NULL AND modul_id IN (
        SELECT id FROM modul WHERE praktikum_id IN ($idList)
    )
")->fetch_assoc()['total'];

// 5. Aktivitas Laporan Terbaru
$recent = $conn->query("
    SELECT m.judul AS modul_judul, u.nama AS mahasiswa_nama, l.tanggal_upload
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN users u ON l.mahasiswa_id = u.id AND u.role = 'mahasiswa'
    WHERE m.praktikum_id IN ($idList)
    ORDER BY l.tanggal_upload DESC
    LIMIT 5
");

?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?= $modulCount ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?= $laporanCount ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?= $belumDinilai ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if ($recent->num_rows > 0): ?>
        <?php while ($log = $recent->fetch_assoc()): ?>
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                <span class="font-bold text-gray-500">
                    <?= strtoupper(substr($log['mahasiswa_nama'], 0, 1)) ?>
                </span>
            </div>
            <div>
                <p class="text-gray-800">
                    <strong><?= htmlspecialchars($log['mahasiswa_nama']) ?></strong> mengumpulkan laporan untuk
                    <strong><?= htmlspecialchars($log['modul_judul']) ?></strong>
                </p>
                <p class="text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($log['tanggal_upload'])) ?></p>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p class="text-gray-500 italic">Belum ada laporan yang masuk.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];

// 1. Praktikum Diikuti
$praktikumCount = $conn->query("SELECT COUNT(*) AS total FROM pendaftaran_praktikum WHERE mahasiswa_id = $mahasiswa_id")->fetch_assoc()['total'];

// 2. Tugas Selesai
$tugasSelesai = $conn->query("SELECT COUNT(*) AS total FROM laporan WHERE mahasiswa_id = $mahasiswa_id")->fetch_assoc()['total'];

// 3. Tugas Menunggu (modul - laporan)
$tugasMenungguQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM modul m
    JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id
    WHERE pp.mahasiswa_id = $mahasiswa_id
    AND m.id NOT IN (
        SELECT modul_id FROM laporan WHERE mahasiswa_id = $mahasiswa_id
    )
");
$tugasMenunggu = $tugasMenungguQuery->fetch_assoc()['total'];

// 4. Notifikasi: laporan terbaru (dinilai atau belum dikumpulkan)
$notifikasi = $conn->query("
    SELECT mo.judul AS modul_judul, l.nilai, l.tanggal_upload, l.id AS laporan_id
    FROM modul mo
    JOIN pendaftaran_praktikum pp ON mo.praktikum_id = pp.praktikum_id
    LEFT JOIN laporan l ON l.modul_id = mo.id AND l.mahasiswa_id = $mahasiswa_id
    WHERE pp.mahasiswa_id = $mahasiswa_id
    ORDER BY l.tanggal_upload DESC, mo.created_at DESC
    LIMIT 5
");
?>

<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-blue-600"><?= $praktikumCount ?></div>
        <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-green-500"><?= $tugasSelesai ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-yellow-500"><?= $tugasMenunggu ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php if ($notifikasi->num_rows > 0): ?>
        <?php while ($n = $notifikasi->fetch_assoc()): ?>
        <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
            <span class="text-xl mr-4">
                <?php if ($n['nilai'] !== null): ?>✅
                <?php elseif ($n['laporan_id'] !== null): ?>📥
                <?php else: ?>⏳
                <?php endif; ?>
            </span>
            <div>
                <?php if ($n['nilai'] !== null): ?>
                Nilai untuk <span class="font-semibold text-blue-600"><?= htmlspecialchars($n['modul_judul']) ?></span>
                telah diberikan.
                <?php elseif ($n['laporan_id'] !== null): ?>
                Laporan untuk <span
                    class="font-semibold text-blue-600"><?= htmlspecialchars($n['modul_judul']) ?></span> telah berhasil
                diunggah.
                <?php else: ?>
                Anda belum mengumpulkan laporan untuk <span
                    class="font-semibold text-blue-600"><?= htmlspecialchars($n['modul_judul']) ?></span>.
                <?php endif; ?>
            </div>
        </li>
        <?php endwhile; ?>
        <?php else: ?>
        <li class="text-gray-500 italic">Belum ada notifikasi.</li>
        <?php endif; ?>
    </ul>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
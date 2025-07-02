<?php
session_start();

// Akses hanya untuk mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

require_once '../config.php';

$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
include 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];

// Ambil semua praktikum yang diikuti mahasiswa
$sql = "
    SELECT p.*
    FROM pendaftaran_praktikum pp
    JOIN praktikum p ON pp.praktikum_id = p.id
    WHERE pp.mahasiswa_id = ?
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$praktikumResult = $stmt->get_result();
?>

<h1 class="text-2xl font-bold mb-6">Praktikum yang Anda Ikuti</h1>

<?php if ($praktikumResult->num_rows > 0): ?>
<div class="space-y-8">
    <?php while ($praktikum = $praktikumResult->fetch_assoc()): ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-blue-600 mb-1"><?= htmlspecialchars($praktikum['nama']) ?></h2>
        <p class="text-gray-700"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
        <a href="detail_praktikum.php?id=<?= $praktikum['id'] ?>"
            class="text-blue-500 underline mb-4 inline-block">Lihat Detail Praktikum</a>

        <h3 class="font-semibold mt-4 mb-2 text-gray-800">Modul & Laporan:</h3>

        <?php
            // Ambil semua modul untuk praktikum ini
            $modulStmt = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ?");
            $modulStmt->bind_param("i", $praktikum['id']);
            $modulStmt->execute();
            $modulResult = $modulStmt->get_result();
        ?>

        <?php if ($modulResult->num_rows > 0): ?>
        <ul class="space-y-3">
            <?php while ($modul = $modulResult->fetch_assoc()): ?>
            <?php
                    // Cek apakah mahasiswa sudah mengumpulkan laporan untuk modul ini
                    $laporanStmt = $conn->prepare("SELECT * FROM laporan WHERE modul_id = ? AND mahasiswa_id = ?");
                    $laporanStmt->bind_param("ii", $modul['id'], $mahasiswa_id);
                    $laporanStmt->execute();
                    $laporan = $laporanStmt->get_result()->fetch_assoc();
                ?>
            <li class="border rounded-md p-4">
                <p class="font-medium"><?= htmlspecialchars($modul['judul']) ?></p>

                <?php if ($laporan): ?>
                <p class="text-green-600 text-sm">✅ Laporan sudah dikumpulkan</p>
                <p class="text-sm text-gray-600">Nilai: <strong><?= $laporan['nilai'] ?? 'Belum dinilai' ?></strong></p>
                <p class="text-sm text-gray-600">Komentar: <?= $laporan['komentar'] ?? '<em>Tidak ada komentar</em>' ?>
                </p>
                <a href="../uploads/<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank"
                    class="text-blue-500 underline text-sm">Lihat Laporan</a>
                <?php else: ?>
                <p class="text-red-600 text-sm">❌ Belum mengumpulkan laporan</p>
                <a href="unggah_laporan.php?modul_id=<?= $modul['id'] ?>" class="text-blue-600 text-sm underline">Unggah
                    Laporan</a>
                <?php endif; ?>
            </li>
            <?php endwhile; ?>
        </ul>
        <?php else: ?>
        <p class="text-sm italic text-gray-500">Belum ada modul untuk praktikum ini.</p>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<p class="text-gray-600">Anda belum mendaftar ke praktikum manapun.</p>
<?php endif; ?>

<?php include 'templates/footer_mahasiswa.php'; ?>
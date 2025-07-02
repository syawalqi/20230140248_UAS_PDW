<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

// Get praktikum ID
$praktikum_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil detail praktikum
$praktikumStmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
$praktikumStmt->bind_param("i", $praktikum_id);
$praktikumStmt->execute();
$praktikum = $praktikumStmt->get_result()->fetch_assoc();

if (!$praktikum) {
    echo "<div class='text-red-600 font-semibold'>Praktikum tidak ditemukan.</div>";
    include 'templates/footer_mahasiswa.php';
    exit();
}

// Upload laporan
$notif = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_laporan'])) {
    $modul_id = intval($_POST['modul_id']);
    $mahasiswa_id = $_SESSION['user_id'];

    if ($_FILES['laporan']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/laporan/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $originalName = basename($_FILES['laporan']['name']);
        $fileName = uniqid() . "_" . $originalName;
        $filePath = $uploadDir . $fileName;

        move_uploaded_file($_FILES['laporan']['tmp_name'], $filePath);

        $stmt = $conn->prepare("INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE file_laporan = VALUES(file_laporan), tanggal_upload = CURRENT_TIMESTAMP");
        $stmt->bind_param("iis", $mahasiswa_id, $modul_id, $filePath);
        $stmt->execute();

        $notif = "<div class='bg-green-100 text-green-800 p-3 rounded mb-6'>Laporan berhasil diunggah.</div>";
    } else {
        $notif = "<div class='bg-red-100 text-red-800 p-3 rounded mb-6'>Gagal mengunggah laporan.</div>";
    }
}

// Ambil modul untuk praktikum ini
$modulStmt = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ? ORDER BY created_at ASC");
$modulStmt->bind_param("i", $praktikum_id);
$modulStmt->execute();
$modulResult = $modulStmt->get_result();
?>

<h1 class="text-2xl font-bold mb-4">Detail Praktikum: <?= htmlspecialchars($praktikum['nama']) ?></h1>
<?= $notif ?>

<?php while ($modul = $modulResult->fetch_assoc()): ?>
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-semibold text-blue-600"><?= htmlspecialchars($modul['judul']) ?></h2>

    <!-- Materi -->
    <?php if (!empty($modul['file_materi'])): ?>
    <p class="mt-2 text-sm">
        <a href="../uploads/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank"
            class="text-blue-500 underline">
            Unduh Materi
        </a>
    </p>
    <?php else: ?>
    <p class="text-sm text-gray-500 mt-2">Materi belum tersedia.</p>
    <?php endif; ?>

    <!-- Upload Laporan -->
    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
        <label class="block font-medium mb-1">Upload Laporan:</label>
        <input type="file" name="laporan" required class="mb-2 block">
        <button type="submit" name="upload_laporan"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Unggah
        </button>
    </form>

    <!-- Informasi Laporan -->
    <?php
        $laporanStmt = $conn->prepare("SELECT * FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?");
        $laporanStmt->bind_param("ii", $_SESSION['user_id'], $modul['id']);
        $laporanStmt->execute();
        $laporan = $laporanStmt->get_result()->fetch_assoc();
    ?>

    <?php if ($laporan): ?>
    <div class="mt-4 text-sm bg-gray-50 p-4 rounded border">
        <p><strong>Laporan:</strong>
            <a href="<?= htmlspecialchars($laporan['file_laporan']) ?>" class="text-blue-500 underline"
                target="_blank">Lihat File</a>
        </p>
        <?php if (!is_null($laporan['nilai'])): ?>
        <p><strong>Nilai:</strong> <?= $laporan['nilai'] ?></p>
        <p><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($laporan['komentar'])) ?></p>
        <?php else: ?>
        <p class="text-yellow-600">Belum dinilai</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endwhile; ?>

<?php include 'templates/footer_mahasiswa.php'; ?>
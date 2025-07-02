<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
include 'templates/header_mahasiswa.php';

$user_id = $_SESSION['user_id'];

// Get list of praktikum and whether the mahasiswa already joined
$sql = "
    SELECT p.*, 
    (SELECT COUNT(*) FROM pendaftaran_praktikum pp 
     WHERE pp.praktikum_id = p.id AND pp.mahasiswa_id = ?) as sudah_ikut
    FROM praktikum p
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1 class="text-2xl font-bold mb-6">Katalog Mata Praktikum</h1>

<div class="grid md:grid-cols-2 gap-6">
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-blue-600"><?= htmlspecialchars($row['nama']) ?></h2>
        <p class="text-gray-600"><?= htmlspecialchars($row['deskripsi']) ?></p>

        <?php if ($row['sudah_ikut']): ?>
        <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded text-sm">Sudah Terdaftar</span>
        <a href="detail_praktikum.php?id=<?= $row['id'] ?>" class="ml-4 text-blue-500 underline">Lihat Detail</a>
        <?php else: ?>
        <form action="daftar_praktikum.php" method="POST">
            <input type="hidden" name="praktikum_id" value="<?= $row['id'] ?>">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Daftar
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>
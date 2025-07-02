<?php
require_once 'config.php';

$query = "SELECT * FROM praktikum ORDER BY created_at DESC";
$result = $conn->query($query);

// Set active page and title for header
$pageTitle = 'Katalog Praktikum';
$activePage = 'katalog';
include 'index/indexheader.php';
?>

<h1 class="text-2xl font-bold mb-6">Katalog Mata Praktikum</h1>

<div class="grid md:grid-cols-2 gap-4">
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold"><?= htmlspecialchars($row['nama']) ?></h2>
        <p class="text-gray-600"><?= htmlspecialchars($row['deskripsi']) ?></p>
        <p class="text-sm text-gray-500"><?= $row['semester'] ?> - <?= $row['tahun_ajaran'] ?></p>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'index/indexfooter.php'; ?>
<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';
require_once '../config.php';

// Ambil laporan gabung dengan informasi mahasiswa dan modul
$sql = "SELECT laporan.*, modul.judul AS judul_modul, users.nama AS nama_mahasiswa
        FROM laporan
        JOIN modul ON laporan.modul_id = modul.id
        JOIN users ON laporan.mahasiswa_id = users.id
        ORDER BY laporan.tanggal_upload DESC";

$result = $conn->query($sql);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Daftar Laporan Masuk</h2>

    <?php if ($result->num_rows > 0): ?>
    <table class="w-full text-left border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 border">Mahasiswa</th>
                <th class="px-4 py-2 border">Modul</th>
                <th class="px-4 py-2 border">Tanggal Upload</th>
                <th class="px-4 py-2 border">Laporan</th>
                <th class="px-4 py-2 border">Nilai</th>
                <th class="px-4 py-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td class="px-4 py-2 border"><?= htmlspecialchars($row['judul_modul']) ?></td>
                <td class="px-4 py-2 border"><?= $row['tanggal_upload'] ?></td>
                <td class="px-4 py-2 border">
                    <a href="../uploads/<?= htmlspecialchars($row['file_laporan']) ?>" class="text-blue-600 underline"
                        target="_blank">Lihat</a>
                </td>
                <td class="px-4 py-2 border">
                    <?= $row['nilai'] ?? '<span class="italic text-gray-400">Belum dinilai</span>' ?></td>
                <td class="px-4 py-2 border">
                    <a href="nilai_laporan.php?id=<?= $row['id'] ?>"
                        class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Nilai</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="text-gray-500 italic">Belum ada laporan yang masuk.</p>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
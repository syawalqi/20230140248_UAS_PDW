<?php
$pageTitle = 'Nilai Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';
require_once '../config.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-500'>ID laporan tidak ditemukan.</p>";
    require_once 'templates/footer.php';
    exit();
}

$laporan_id = (int)$_GET['id'];

// Ambil data laporan
$stmt = $conn->prepare("SELECT laporan.*, modul.judul AS judul_modul, users.nama AS nama_mahasiswa 
                        FROM laporan 
                        JOIN modul ON laporan.modul_id = modul.id 
                        JOIN users ON laporan.mahasiswa_id = users.id 
                        WHERE laporan.id = ?");
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-red-500'>Laporan tidak ditemukan.</p>";
    require_once 'templates/footer.php';
    exit();
}

$laporan = $result->fetch_assoc();

// Proses update nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = (int)$_POST['nilai'];
    $komentar = trim($_POST['komentar']);

    $update = $conn->prepare("UPDATE laporan SET nilai = ?, komentar = ? WHERE id = ?");
    $update->bind_param("isi", $nilai, $komentar, $laporan_id);
    $update->execute();

    header("Location: laporan.php");
    exit();
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Penilaian Laporan</h2>

    <div class="mb-4">
        <p><strong>Mahasiswa:</strong> <?= htmlspecialchars($laporan['nama_mahasiswa']) ?></p>
        <p><strong>Modul:</strong> <?= htmlspecialchars($laporan['judul_modul']) ?></p>
        <p><strong>Tanggal Upload:</strong> <?= $laporan['tanggal_upload'] ?></p>
        <p><strong>File:</strong>
            <a href="../uploads/<?= htmlspecialchars($laporan['file_laporan']) ?>" class="text-blue-600 underline"
                target="_blank">Lihat Laporan</a>
        </p>
    </div>

    <form method="post" class="space-y-4">
        <div>
            <label for="nilai" class="block font-medium mb-1">Nilai</label>
            <input type="number" name="nilai" id="nilai" value="<?= $laporan['nilai'] ?? '' ?>" required
                class="w-full p-2 border rounded" min="0" max="100">
        </div>

        <div>
            <label for="komentar" class="block font-medium mb-1">Komentar</label>
            <textarea name="komentar" id="komentar" rows="4"
                class="w-full p-2 border rounded"><?= htmlspecialchars($laporan['komentar'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Simpan Penilaian
        </button>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
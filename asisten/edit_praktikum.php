<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Edit Praktikum';
$activePage = 'modul';

$asisten_id = $_SESSION['user_id'];
$praktikum_id = $_GET['id'] ?? null;

if (!$praktikum_id) {
    echo "ID praktikum tidak ditemukan.";
    exit();
}

// Ambil data praktikum
$stmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ? AND asisten_id = ?");
$stmt->bind_param("ii", $praktikum_id, $asisten_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikum = $result->fetch_assoc();

if (!$praktikum) {
    echo "Data praktikum tidak ditemukan atau tidak memiliki akses.";
    exit();
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);

    $update = $conn->prepare("UPDATE praktikum SET nama = ?, deskripsi = ? WHERE id = ? AND asisten_id = ?");
    $update->bind_param("ssii", $nama, $deskripsi, $praktikum_id, $asisten_id);
    $update->execute();

    header("Location: modul.php?praktikum_id=$praktikum_id");
    exit();
}

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md max-w-xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Edit Praktikum</h2>
    <form method="post">
        <div class="mb-4">
            <label for="nama_praktikum" class="block font-medium mb-1">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" id="nama_praktikum"
                value="<?= htmlspecialchars($praktikum['nama']) ?>" class="w-full p-2 border border-gray-300 rounded"
                required>
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block font-medium mb-1">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="w-full p-2 border border-gray-300 rounded"
                rows="4"><?= htmlspecialchars($praktikum['deskripsi']) ?></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan
            Perubahan</button>
        <a href="modul.php?praktikum_id=<?= $praktikum_id ?>" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
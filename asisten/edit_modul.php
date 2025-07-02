<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Ambil ID modul dari URL
$modul_id = $_GET['id'] ?? null;
if (!$modul_id) {
    header("Location: modul.php");
    exit();
}

// Ambil data modul
$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $modul_id);
$stmt->execute();
$result = $stmt->get_result();
$modul = $result->fetch_assoc();

if (!$modul) {
    header("Location: modul.php");
    exit();
}

// Update saat submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $fileMateriName = $modul['file_materi']; // default: file lama

    if (!empty($_FILES['file_materi']['name'])) {
        $allowedTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $fileType = $_FILES['file_materi']['type'];

        if (in_array($fileType, $allowedTypes)) {
            // Hapus file lama jika ada
            if (!empty($fileMateriName)) {
                $oldPath = '../uploads/' . $fileMateriName;
                if (file_exists($oldPath)) unlink($oldPath);
            }

            // Upload file baru
            $safeName = uniqid() . '_' . basename($_FILES['file_materi']['name']);
            $uploadPath = '../uploads/' . $safeName;
            move_uploaded_file($_FILES['file_materi']['tmp_name'], $uploadPath);
            $fileMateriName = $safeName;
        }
    }

    $stmt = $conn->prepare("UPDATE modul SET judul = ?, file_materi = ? WHERE id = ?");
    $stmt->bind_param("ssi", $judul, $fileMateriName, $modul_id);
    $stmt->execute();

    header("Location: modul.php?praktikum_id=" . $modul['praktikum_id']);
    exit();
}

// Setelah semua proses logika, baru tampilkan tampilan
$pageTitle = 'Edit Modul';
$activePage = 'modul';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-6 max-w-xl">
    <h2 class="text-2xl font-bold mb-4">Edit Modul</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="judul" class="block font-medium mb-1">Judul Modul</label>
            <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($modul['judul']) ?>"
                class="w-full p-2 border border-gray-300 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">File Materi Saat Ini</label>
            <?php if ($modul['file_materi']): ?>
            <a href="../uploads/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank"
                class="text-blue-600 underline text-sm">Lihat File</a>
            <?php else: ?>
            <p class="text-gray-500 italic text-sm">Tidak ada file</p>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="file_materi" class="block font-medium mb-1">Upload File Baru (Opsional)</label>
            <input type="file" name="file_materi" id="file_materi" accept=".pdf,.docx" class="w-full p-2">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan
            Perubahan</button>
        <a href="modul.php?praktikum_id=<?= $modul['praktikum_id'] ?>"
            class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
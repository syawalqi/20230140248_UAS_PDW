<?php
session_start();

// Akses hanya untuk mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

$mahasiswa_id = $_SESSION['user_id'];
$modul_id = $_GET['modul_id'] ?? null;

// Validasi modul_id
if (!$modul_id) {
    echo "Modul tidak ditemukan.";
    exit();
}

// Ambil data modul dan praktikum-nya
$stmt = $conn->prepare("
    SELECT m.judul AS modul_judul, p.nama AS praktikum_nama
    FROM modul m
    JOIN praktikum p ON m.praktikum_id = p.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $modul_id);
$stmt->execute();
$modul = $stmt->get_result()->fetch_assoc();

if (!$modul) {
    echo "Modul tidak ditemukan.";
    exit();
}

// Tangani submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['file_laporan']['name'])) {
        $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($_FILES['file_laporan']['type'], $allowedTypes)) {
            $uploadDir = '../uploads/';
            $safeName = uniqid() . '_' . basename($_FILES['file_laporan']['name']);
            $uploadPath = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $uploadPath)) {
                // Simpan ke database
                $stmt = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $modul_id, $mahasiswa_id, $safeName);
                $stmt->execute();

                // Redirect ke my_courses.php
                header("Location: my_courses.php");
                exit();
            } else {
                $error = "Gagal mengunggah file.";
            }
        } else {
            $error = "Tipe file tidak diizinkan. Gunakan PDF atau DOCX.";
        }
    } else {
        $error = "Silakan pilih file laporan untuk diunggah.";
    }
}

$pageTitle = 'Unggah Laporan';
$activePage = '';
include 'templates/header_mahasiswa.php';
?>

<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">Unggah Laporan</h1>
    <p class="mb-2"><strong>Praktikum:</strong> <?= htmlspecialchars($modul['praktikum_nama']) ?></p>
    <p class="mb-4"><strong>Modul:</strong> <?= htmlspecialchars($modul['modul_judul']) ?></p>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="file_laporan" class="block mb-1 font-medium">Pilih File Laporan (.pdf / .docx)</label>
            <input type="file" name="file_laporan" accept=".pdf,.docx" class="w-full p-2 border rounded" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Unggah</button>
        <a href="my_courses.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';

$asisten_id = $_SESSION['user_id'];

// Tambah Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_praktikum'])) {
    $nama = trim($_POST['nama_praktikum']);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama)) {
        $stmt = $conn->prepare("INSERT INTO praktikum (nama, deskripsi, asisten_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nama, $deskripsi, $asisten_id);
        $stmt->execute();
        header("Location: modul.php");
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_praktikum_id'])) {
    $hapusId = (int)$_POST['hapus_praktikum_id'];

    // Hapus laporan dan file terkait modul
    $modulResult = $conn->query("SELECT id, file_materi FROM modul WHERE praktikum_id = $hapusId");
    while ($modul = $modulResult->fetch_assoc()) {
        $modul_id = (int)$modul['id'];

        // Hapus laporan yang terkait dengan modul
        $conn->query("DELETE FROM laporan WHERE modul_id = $modul_id");

        // Hapus file materi jika ada
        if (!empty($modul['file_materi'])) {
            $filePath = "../uploads/" . $modul['file_materi'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus modul
        $conn->query("DELETE FROM modul WHERE id = $modul_id");
    }

    // Hapus pendaftaran praktikum terkait
    $conn->query("DELETE FROM pendaftaran_praktikum WHERE praktikum_id = $hapusId");

    // Terakhir, hapus praktikum
    $conn->query("DELETE FROM praktikum WHERE id = $hapusId AND asisten_id = $asisten_id");

    header("Location: modul.php");
    exit();
}



// Ambil daftar praktikum milik asisten
$praktikumList = $conn->query("SELECT id, nama FROM praktikum WHERE asisten_id = $asisten_id");

// Ambil praktikum_id dari URL
$praktikum_id = $_GET['praktikum_id'] ?? null;

// Tambah Modul
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_modul'])) {
    $judul = trim($_POST['judul']);
    $praktikum_id_form = (int)$_POST['praktikum_id'];
    $fileMateriName = null;

    if (!empty($_FILES['file_materi']['name'])) {
        $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($_FILES['file_materi']['type'], $allowedTypes)) {
            $uploadDir = '../uploads/';
            $safeName = uniqid() . '_' . basename($_FILES['file_materi']['name']);
            move_uploaded_file($_FILES['file_materi']['tmp_name'], $uploadDir . $safeName);
            $fileMateriName = $safeName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul, file_materi) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $praktikum_id_form, $judul, $fileMateriName);
    $stmt->execute();
    header("Location: modul.php?praktikum_id=$praktikum_id_form");
    exit();
}

// Ambil modul jika praktikum dipilih
$modulList = [];
if ($praktikum_id) {
    $stmt = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ?");
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    $modulList = $stmt->get_result();
}

require_once 'templates/header.php';
?>

<!-- Form Tambah Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-bold mb-4">Tambah Mata Praktikum</h3>
    <form method="post">
        <div class="mb-4">
            <label for="nama_praktikum" class="block font-medium mb-1">Nama Praktikum</label>
            <input type="text" name="nama_praktikum" id="nama_praktikum" required class="w-full p-2 border rounded">
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block font-medium mb-1">Deskripsi Praktikum</label>
            <textarea name="deskripsi" id="deskripsi" rows="3" class="w-full p-2 border rounded resize-none"></textarea>
        </div>

        <button type="submit" name="tambah_praktikum"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Tambah Praktikum
        </button>
    </form>
</div>


<!-- Pilih Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form method="get">
        <label for="praktikum_id" class="block mb-2 font-semibold text-gray-700">Pilih Mata Praktikum:</label>
        <select name="praktikum_id" id="praktikum_id" onchange="this.form.submit()" class="p-2 border rounded w-full">
            <option value="">-- Pilih Praktikum --</option>
            <?php while ($p = $praktikumList->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>" <?= ($praktikum_id == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nama']) ?>
            </option>
            <?php endwhile; ?>
        </select>
    </form>
</div>

<!-- Daftar Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-lg font-semibold mb-3">Daftar Praktikum Anda</h3>
    <ul class="space-y-2">
        <?php
        $praktikumListResult = $conn->query("SELECT id, nama, deskripsi FROM praktikum WHERE asisten_id = $asisten_id");
        while ($p = $praktikumListResult->fetch_assoc()):
        ?>
        <li class="border-b pb-2">
            <div class="flex justify-between items-center">
                <div>
                    <span class="font-semibold"><?= htmlspecialchars($p['nama']) ?></span>
                    <?php if (!empty($p['deskripsi'])): ?>
                    <p class="text-sm text-gray-500 italic"><?= htmlspecialchars($p['deskripsi']) ?></p>
                    <?php endif; ?>
                </div>
                <form method="post" onsubmit="return confirm('Yakin ingin menghapus praktikum ini?');">
                    <input type="hidden" name="hapus_praktikum_id" value="<?= $p['id'] ?>">
                    <button type="submit"
                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Hapus</button>

                    <a href="edit_praktikum.php?id=<?= $p['id'] ?>"
                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                </form>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>
</div>


<?php if ($praktikum_id): ?>
<!-- Form Tambah Modul -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-bold mb-4">Tambah Modul</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">

        <div class="mb-4">
            <label for="judul" class="block font-medium mb-1">Judul Modul</label>
            <input type="text" name="judul" id="judul" required class="w-full p-2 border rounded">
        </div>

        <div class="mb-4">
            <label for="file_materi" class="block font-medium mb-1">File Materi (PDF/DOCX)</label>
            <input type="file" name="file_materi" id="file_materi" accept=".pdf,.docx" class="w-full p-2">
        </div>

        <button type="submit" name="tambah_modul" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Tambah Modul
        </button>
    </form>
</div>

<!-- Daftar Modul -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold mb-4">Daftar Modul</h3>
    <?php if ($modulList->num_rows > 0): ?>
    <ul class="space-y-4">
        <?php while ($modul = $modulList->fetch_assoc()): ?>
        <li class="border-b pb-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold"><?= htmlspecialchars($modul['judul']) ?></p>
                    <?php if ($modul['file_materi']): ?>
                    <a href="../uploads/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank"
                        class="text-sm text-blue-600 underline">Lihat Materi</a>
                    <?php else: ?>
                    <p class="text-sm text-gray-500 italic">Belum ada file</p>
                    <?php endif; ?>
                </div>
                <form method="post" action="hapus_modul.php"
                    onsubmit="return confirm('Yakin ingin menghapus modul ini?');">
                    <input type="hidden" name="modul_id" value="<?= $modul['id'] ?>">
                    <input type="hidden" name="praktikum_id" value="<?= $praktikum_id ?>">
                    <button type="submit"
                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Hapus</button>
                    <a href="edit_modul.php?id=<?= $modul['id'] ?>"
                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 mr-2">Edit</a>
                </form>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>
    <?php else: ?>
    <p class="text-gray-500 italic">Belum ada modul untuk praktikum ini.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
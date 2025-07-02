<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $email, $role, $id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$pageTitle = 'Edit User';
$activePage = 'users';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Pengguna</h2>
    <form method="POST">
        <label class="block mb-2">Nama</label>
        <input type="text" name="nama" class="w-full p-2 border rounded mb-4"
            value="<?= htmlspecialchars($data['nama']) ?>" required>

        <label class="block mb-2">Email</label>
        <input type="email" name="email" class="w-full p-2 border rounded mb-4"
            value="<?= htmlspecialchars($data['email']) ?>" required>

        <label class="block mb-2">Role</label>
        <select name="role" class="w-full p-2 border rounded mb-4">
            <option value="mahasiswa" <?= $data['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
            <option value="asisten" <?= $data['role'] === 'asisten' ? 'selected' : '' ?>>Asisten</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
        <a href="users.php" class="ml-4 text-gray-600 hover:underline">Kembali</a>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
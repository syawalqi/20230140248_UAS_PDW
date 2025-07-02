<?php
require_once '../config.php';
$pageTitle = 'Manajemen User';
$activePage = 'users';
require_once 'templates/header.php';

// Ambil semua user
$result = $conn->query("SELECT id, nama, email, role FROM users");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Pengguna</h2>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b text-left">Nama</th>
                <th class="py-2 px-4 border-b text-left">Email</th>
                <th class="py-2 px-4 border-b text-left">Role</th>
                <th class="py-2 px-4 border-b text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['nama']) ?></td>
                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['email']) ?></td>
                <td class="py-2 px-4 border-b capitalize"><?= htmlspecialchars($row['role']) ?></td>
                <td class="py-2 px-4 border-b space-x-2">
                    <a href="edit_user.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                    <a href="delete_user.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline"
                        onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>
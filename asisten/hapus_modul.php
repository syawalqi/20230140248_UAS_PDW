<?php
require_once '../config.php';
session_start();

// Cek apakah user sudah login dan memiliki role asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Pastikan data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modul_id = (int)$_POST['modul_id'];
    $praktikum_id = (int)$_POST['praktikum_id'];

    // Ambil nama file materi (jika ada)
    $stmt = $conn->prepare("SELECT file_materi FROM modul WHERE id = ?");
    $stmt->bind_param("i", $modul_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $modul = $result->fetch_assoc();

        // Hapus file jika ada
        if (!empty($modul['file_materi'])) {
            $filePath = "../uploads/" . $modul['file_materi'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus dari database
        $deleteStmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
        $deleteStmt->bind_param("i", $modul_id);
        $deleteStmt->execute();
    }

    header("Location: modul.php?praktikum_id=$praktikum_id");
    exit();
} else {
    // Jika bukan POST request
    header("Location: modul.php");
    exit();
}
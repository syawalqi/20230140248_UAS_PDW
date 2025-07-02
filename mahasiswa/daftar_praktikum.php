<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit();
}

require_once '../config.php';

$mahasiswa_id = $_SESSION['user_id'];
$praktikum_id = intval($_POST['praktikum_id']);

// Cegah duplikat pendaftaran
$stmt = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
$stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert pendaftaran baru
    $insert = $conn->prepare("INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
    $insert->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $insert->execute();
}

header("Location: courses.php");
exit();
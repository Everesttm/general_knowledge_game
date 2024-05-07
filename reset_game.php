<?php
session_start();
require 'database.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['userID'];
$query = "UPDATE j_user SET score = 0 WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$stmt->close();
?>

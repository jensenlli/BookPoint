<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_POST['book_id']);

// Останавливаем активную сессию
$sql = "UPDATE reading_sessions 
        SET is_active = FALSE,
            total_seconds = total_seconds + TIMESTAMPDIFF(SECOND, last_updated, NOW()),
            last_updated = NOW()
        WHERE user_id = ?
          AND book_id = ?
          AND is_active = TRUE";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $bookId);
$stmt->execute();

echo json_encode(['status' => 'success']);
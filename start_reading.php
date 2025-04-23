<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_POST['book_id']);

// Создаем новую сессию чтения
$sql = "INSERT INTO reading_sessions 
        (user_id, book_id, start_time, last_updated, is_active) 
        VALUES (?, ?, NOW(), NOW(), TRUE)
        ON DUPLICATE KEY UPDATE 
            is_active = TRUE,
            last_updated = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $bookId);
$stmt->execute();

echo json_encode(['status' => 'success']);
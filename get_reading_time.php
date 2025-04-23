<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_GET['book_id']);

// Получаем общее время чтения (все сессии)
$sql = "SELECT COALESCE(SUM(total_seconds), 0) as total_seconds 
        FROM reading_sessions 
        WHERE user_id = ? 
          AND book_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $bookId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'total_seconds' => (int)$result['total_seconds']
]);
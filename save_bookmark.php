<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_POST['book_id']);
$page = intval($_POST['page']);
$readingTime = isset($_POST['reading_time']) ? intval($_POST['reading_time']) : 0;

try {
    // Используем UPSERT (INSERT + UPDATE)
    $sql = "INSERT INTO bookmarks 
            (user_id, book_id, page, reading_time, created_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                page = VALUES(page),
                reading_time = reading_time + VALUES(reading_time),
                updated_at = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $bookId, $page, $readingTime);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка сохранения: " . $stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'total_time' => getTotalReadingTime($conn, $userId, $bookId)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Функция для получения общего времени чтения книги
function getTotalReadingTime($conn, $userId, $bookId) {
    $sql = "SELECT SUM(reading_time) as total 
            FROM bookmarks 
            WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

$conn->close();
?>
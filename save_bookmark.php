<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_POST['book_id']);
$page = intval($_POST['page']);

// Удаляем предыдущие закладки (только где нет цитат)
$deleteSql = "DELETE FROM bookmarks 
              WHERE user_id = ? 
                AND book_id = ? 
                AND quote_text IS NULL";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $userId, $bookId);
$deleteStmt->execute();

// Вставляем новую закладку
$insertSql = "INSERT INTO bookmarks (user_id, book_id, page, created_at)
              VALUES (?, ?, ?, NOW())";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("iii", $userId, $bookId, $page);

if ($insertStmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$deleteStmt->close();
$insertStmt->close();
$conn->close();
?>
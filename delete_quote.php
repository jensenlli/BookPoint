<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    die('Unauthorized');
}

$userId = $_SESSION['user']['id'];
$quoteId = intval($_POST['quote_id']);

// Проверяем принадлежность цитаты пользователю
$sql = "DELETE FROM bookmarks 
        WHERE id = ? AND user_id = ? AND quote_text IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quoteId, $userId);

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
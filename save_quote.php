<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    die('Unauthorized');
}

$userId = $_SESSION['user']['id'];
$bookId = intval($_POST['book_id']);
$page = intval($_POST['page']);
$quoteText = trim($_POST['quote_text']);

if (empty($quoteText)) {
    die('Empty quote');
}

$sql = "INSERT INTO bookmarks (user_id, book_id, page, quote_text, created_at)
        VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $userId, $bookId, $page, $quoteText);

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}
?>
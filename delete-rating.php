<?php
session_start();
include('config/dbConnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($bookId > 0 && $userId > 0) {
        $sql_delete = "DELETE FROM ratings WHERE book_id = ? AND user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $bookId, $userId);
        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
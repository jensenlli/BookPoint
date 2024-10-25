<?php
session_start(); // Стартуем сессию
include('config/dbConnect.php');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user']['id'])) {
    echo "Пользователь авторизован";
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)$_POST['book_id']; // Получаем ID книги из формы
    $userId = $_SESSION['user']['id']; // Получаем ID пользователя из сессии

    // SQL-запрос для добавления в избранное
    $addFavoriteQuery = "INSERT INTO favorites (user_id, book_id) VALUES (?, ?)";
    $stmt = $conn->prepare($addFavoriteQuery);
    $stmt->bind_param("ii", $userId, $bookId);

    if ($stmt->execute()) {
        echo 'Книга добавлена в избранное.';
    } else {
        echo 'Не удалось добавить книгу в избранное: ' . $stmt->error;
    }   
}
header("Location: mybook.php?auth_required=true");
?>

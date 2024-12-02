<?php
session_start();
include('config/dbConnect.php');

// Проверка, был ли отправлен POST-запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем идентификатор книги
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $userId = $_SESSION['user']['id'];

    // Проверка, что идентификатор книги корректен
    if ($bookId > 0) {
        // Подготовка SQL-запроса для удаления книги из избранного
        $sql = "DELETE FROM favorites WHERE book_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        
        // Биндинг параметров
        $stmt->bind_param("ii", $bookId, $userId);

        // Выполнение запроса
        if ($stmt->execute()) {
            // Успешное удаление, можно перенаправить пользователя
            header("Location: mybook.php?auth_required");
        } else {
            // Ошибка при удалении
            echo "Ошибка при удалении книги из избранного: " . $stmt->error;
        }
    } else {
        echo "Некорректный идентификатор книги.";
    }
} else {
    echo "Неверный запрос.";
}
?>

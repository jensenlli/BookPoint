<?php
session_start();
include('config/dbConnect.php');

// Проверяем, что данные переданы
if (isset($_POST['book_id'], $_POST['rating'])) {
    $book_id = intval($_POST['book_id']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user']['id'];

    // Проверяем, что рейтинг в диапазоне от 1 до 10
    if ($rating >= 1 && $rating <= 10) {
        // Проверка, существует ли запись для данного user_id и book_id
        $sqlCheck = "SELECT * FROM favorites WHERE book_id = ? AND user_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $book_id, $user_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            // Запись существует, обновляем рейтинг
            $sqlUpdate = "UPDATE favorites SET rating_user = ? WHERE book_id = ? AND user_id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("iii", $rating, $book_id, $user_id);
            if ($stmtUpdate->execute()) {
                echo "Рейтинг успешно обновлен.";
            } else {
                echo "Ошибка при обновлении рейтинга: " . $stmtUpdate->error;
            }
        } else {
            // Запись не существует, вставляем новую
            $sqlInsert = "INSERT INTO favorites (book_id, user_id, rating_user) VALUES (?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("iii", $book_id, $user_id, $rating);
            if ($stmtInsert->execute()) {
                echo "Рейтинг успешно сохранен.";
            } else {
                echo "Ошибка при сохранении рейтинга: " . $stmtInsert->error;
            }
        }
    } else {
        echo "Неверный рейтинг.";
    }
} else {
    echo "Необходимые данные не переданы.";
}

$conn->close();
?>

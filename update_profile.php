<?php
session_start();
include('config/dbConnect.php');

// Проверяем, что форма была отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $newFullName = htmlspecialchars(trim($_POST['full_name']));
    $newEmail = htmlspecialchars(trim($_POST['email']));

    // Проверяем email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        echo "Неверный email адрес";
        exit;
    }

    // Получаем текущее значение из базы данных для сравнения
    $query = "SELECT full_name, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user']['id']);
    $stmt->execute();
    $currentData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Определяем значения для обновления
    $updatedFullName = $newFullName !== $currentData['full_name'] ? $newFullName : $currentData['full_name'];
    $updatedEmail = $newEmail !== $currentData['email'] ? $newEmail : $currentData['email'];

    // Обновляем данные пользователя в базе данных
    $query = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    // Проверяем, что подготовленное выражение успешно создано
    if (!$stmt) {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        exit;
    }

    // Связываем переменные с параметрами запроса
    $stmt->bind_param("ssi", $updatedFullName, $updatedEmail, $_SESSION['user']['id']);

    // Выполняем запрос
    if ($stmt->execute()) {
        echo "Профиль успешно обновлен!";
        
        // Обновляем данные пользователя в сессии
        $_SESSION['user']['full_name'] = $updatedFullName;
        $_SESSION['user']['email'] = $updatedEmail;
        
        // Проверяем результат обновления
        $result = $conn->query("SELECT * FROM users WHERE id = " . intval($_SESSION['user']['id']));
        if ($result && $row = $result->fetch_assoc()) {
            echo "Текущие данные пользователя: ";
            print_r($row);
        } else {
            echo "Не удалось получить данные пользователя после обновления.";
        }
    } else {
        echo "Не удалось обновить профиль. Ошибка: " . $stmt->error;
    }

    // Закрываем подготовленное выражение и соединение с базой данных
    $stmt->close();
    $conn->close();

    // Перенаправляем на страницу профиля
    header("Location: profil.php");
    exit;
}
?>
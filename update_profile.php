<?php
session_start();
include('config/dbConnect.php');

// Проверяем, что форма была отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $newFullName = htmlspecialchars(trim($_POST['full_name']));
    $newEmail = htmlspecialchars(trim($_POST['email']));
    $oldPassword = htmlspecialchars(trim($_POST['old_password']));
    $newPassword = htmlspecialchars(trim($_POST['new_password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirm_password']));

    // Проверяем email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        echo "Неверный email адрес";
        exit;
    }

    // Получаем текущее значение из базы данных для сравнения
    $query = "SELECT full_name, email, password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user']['id']);
    $stmt->execute();
    $currentData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Определяем значения для обновления
    $updatedFullName = $newFullName !== $currentData['full_name'] ? $newFullName : $currentData['full_name'];
    $updatedEmail = $newEmail !== $currentData['email'] ? $newEmail : $currentData['email'];
    $updatedPassword = $currentData['password']; // По умолчанию оставляем старый пароль

    // Проверяем, совпадает ли старый пароль
    if (md5($oldPassword) !== $currentData['password']) {
        $_SESSION['message'] = 'Старый пароль неверен.';
        exit;
    }

    // Проверяем, нужно ли обновить пароль
    if (!empty($newPassword)) {
        echo "не пусто";
        if ($newPassword === $confirmPassword) {
            $updatedPassword = md5($newPassword); // Кодируем новый пароль
        } else {
            $_SESSION['message'] = 'Пароли не совпадают.';
            exit;
        }
    }

    // Обновляем данные пользователя в базе данных
    $query = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    // Проверяем, что подготовленное выражение успешно создано
    if (!$stmt) {
        echo "Ошибка при подготовке запроса: " . $conn->error;
        exit;
    }

    // Связываем переменные с параметрами запроса
    $stmt->bind_param("sssi", $updatedFullName, $updatedEmail, $updatedPassword, $_SESSION['user']['id']);

    // Выполняем запрос
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = 'Данные успешно обновлены!';
            
            // Обновляем данные пользователя в сессии
            $_SESSION['user']['full_name'] = $updatedFullName;
            $_SESSION['user']['email'] = $updatedEmail;
        } else {
            echo "Нет изменений для обновления.";
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

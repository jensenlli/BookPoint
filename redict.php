<?php
session_start(); // Убедитесь, что сессия начата

// Проверяем, установлен ли идентификатор пользователя в сессии
if (isset($_SESSION['user']['id'])) {
    // Если пользователь авторизован, перенаправляем его на mybook.php с параметром auth_required
    header("Location: mybook.php?auth_required");
    exit; // Выход из скрипта после редиректа
} else {
    // Если пользователь не авторизован, перенаправляем его на mybook.php 
    header("Location: mybook.php?");
    exit; // Выход из скрипта после редиректа
}
?>
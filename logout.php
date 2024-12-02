<?php
session_start();
//unset($_SESSION['user']);
session_unset(); // Очищает все данные сессии
session_destroy(); // Удаляет саму сессию
header('Location: index.php');
?>
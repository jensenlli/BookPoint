<?php
session_start();
include('../config/dbConnect.php');

$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = $_POST['password'];
$repit_password = $_POST['repit_password'];

// Проверка совпадения паролей
if ($password !== $repit_password) {
    $_SESSION['message'] = 'Пароли не совпадают! Попробуйте ещё раз.';
    header('Location: registr.php');
    exit();
}

// Проверка уникальности email
$email_check = mysqli_query($conn, "SELECT * FROM `users` WHERE `email` = '$email'");
$email_count = mysqli_num_rows($email_check);

if ($email_count > 0) {
    $_SESSION['message'] = 'Этот email уже зарегистрирован. Введите другой email.';
    header('Location: registr.php');
    exit();
}

// Если все проверки пройдены, продолжаем регистрацию
if ($password === $repit_password){
    $password = md5($password);
    mysqli_query($conn, "INSERT INTO `users` (`id`, `full_name`, `email`, `password`) VALUES (NULL, '$full_name', '$email','$password')");
    
    $_SESSION['message'] = 'Регистрация прошла успешно';
    header('Location: ../reg/reg.php');
}
?>

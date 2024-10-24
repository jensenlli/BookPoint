<?php
session_start();
include('../config/dbConnect.php');

$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = $_POST['password'];
$repit_password = $_POST['repit_password'];

if ($password === $repit_password){
    $password = md5($password);
    mysqli_query($conn, "INSERT INTO `users` (`id`, `full_name`, `email`, `password`) VALUES (NULL, '$full_name', '$email','$password')");
    

    $_SESSION['message'] = 'Регистрация прошла успешно';
    header('Location: ../reg/reg.php');
}
else{
    $_SESSION['message'] = 'Пароли не совпадают! Попробуйте ещё раз.';
    header('Location: /registr.php');
}

?>


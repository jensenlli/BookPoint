<?php
session_start();
include('../config/dbConnect.php');

$email = $_POST['email'];
$password = md5($_POST['password']);


$check_user = mysqli_query($conn, "SELECT * FROM `users` WHERE `email`= '$email' AND `password`='$password'");

if (mysqli_num_rows($check_user) > 0){

    $user = mysqli_fetch_assoc($check_user);

    $_SESSION['user'] = [
        "id" => $user['id'],
        "full_name" => $user['full_name'],
        "email" => $user['email']
    ];
    header('Location: ../index.php');

}else{
    $_SESSION['message'] = 'Неверный логин или пароль ';
    header('Location: reg.php');
}
?>
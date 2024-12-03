<?php
session_start();
include('config/dbConnect.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../partials/css/header.css">
</head>
<header class="header">
    <button class="banner">
        <a href="/index.php">
            BookPoint
        </a>
    </button>
    <div class="rightbar">
        <div class="navbar">
            <a class="button" href="/index.php">Каталог</a>
            <a class="button" href="/redict.php" style = 'width: 180px';>Моя библиотека</a>
            <p class='userakk'>
                <?php
                    if ($_SESSION['user']['full_name'] <> NULL){
                        $_SESSION['userakk'] = $_SESSION['user']['full_name'];
                        $link = "/profil.php";
                    }else{
                        $_SESSION['userakk'] = 'Вход';
                        $link = "../reg/reg.php";
                    }
                ?>
            </p>
            <a class="button" href="<?= $link ?>"><?= $_SESSION['userakk'] ?></a>
        </div>
    </div>
</header>
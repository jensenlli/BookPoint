<?php
    session_start();
    include('config/dbConnect.php');

    if (!$_SESSION['user']) {
        header('Location: index.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="partials/css/footer.css">
    <link rel="stylesheet" href="partials/css/header.css">
    <link rel="stylesheet" href="css/profil.css">
    
    <title>BookPoint</title>
</head>


<body>
<div class="wrapper">
    <?php include('header.php'); ?>
        <div class="page">
                <img src="media/image3.jpg" class="imgreg" style="height: 700px;">

            <main class="akk_info">
                <form>

                <h1 class="zag">Имя пользователя</h1>
                <p class="zag2"><?=$_SESSION['user']['full_name']?></p>
                <ol> </ol>
                <h1 class="zag">Email</h1>
                <p class="zag2"><?=$_SESSION['user']['email']?></p>
                <ol> </ol>
                <a class="w-80 btn btn-lg btn-primary" type="submit"  href="user_rating_list.php">Все оцененные книги</a>
                <ol> </ol>
                <a class="w-80 btn btn-lg btn-primary" type="submit"  href="quote_list.php">Мои цитаты</a>
                <ol> </ol>
                <a class="w-80 btn btn-lg btn-primary" type="submit"  href="statistics.php">Моя статистика</a>
                <ol> </ol>
                <ol> </ol>
                <ol> </ol>
                <a class="w-80 btn btn-lg btn-primary" type="submit"  href="red_profile.php">Редактировать профиль</a>
                <ol> </ol>
                <a class="w-80 btn btn-lg btn-primary" type="submit"  href="logout.php">Выйти из аккаунта</a>

                </form>
            </main>

        </div>
        <?php include('partials/footer.php'); ?>
    </div>
    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
<?php
session_start();
include('../config/dbConnect.php');


if ($_SESSION['user']) {
    header('Location: profil.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../partials/css/footer.css">
    <link rel="stylesheet" href="../partials/css/header.css">
    <link rel="stylesheet" href="../css/signin.css">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>BookPoint</title>
</head>

<body>
    <div class="wrapper2">
        <?php include('../header.php'); ?>
        <div class="page1">
            <div class="filter2">
                <img src="/media/input/reg.png" class="imgreg">
            </div>

            <main class="form-signin">
                <form action = "signup.php" method="post">

                    <h1 class="h3 mb-3 fw-normal">Пожалуйста, введите данные</h1>

                    <div class="form-floating">
                        <input type="text" class="form-control" id="floatingInput" name="full_name" placeholder="Иванов Иван">
                        <label for="floatingInput">Фамилия Имя</label>
                    </div>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="floatingInput" name="email" placeholder="name@example.com">
                        <label for="floatingInput">Email адрес</label>
                    </div>
                    <ol></ol>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Пароль">
                        <label for="floatingPassword">Пароль</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="floatingPassword" name="repit_password" placeholder="Пароль">
                        <label for="floatingPassword">Подтвердите пароль</label>
                    </div>
                    <ol></ol>

                    <button class="w-100 btn btn-lg btn-primary" type="submit" link="/index.php">Создать</button>
                    <label class="akk">У Вас уже есть аккаунт?</label>
                    <ol><a href="/index.php" class="createAkk">Авторизоваться</a></ol>

                    <p class="msg">
                        <?php
                            echo $_SESSION['message'];
                            unset($_SESSION['message']); 
                         ?>
                    </p>
                </form>
            </main>


        </div>
        <?php include('../partials/footer.php'); ?>
    </div>
    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
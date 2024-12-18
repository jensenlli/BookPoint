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
    
    <title>BookPoint | Редактирование профиля</title>
</head>

<body>
<div class="wrapper">
    <?php include('header.php'); ?>
        <div class="page">
                <img src="media/image2.jpg" class="imgreg">

            <main class="akk_info">
                <form method="POST" action="update_profile.php">
                    <h1 class="zag" style="font-size: 30px">Редактирование профиля</h1>
                    <ol> </ol>
                    <!-- Поле для имени пользователя -->
                    <h1 class="zag" style = "margin-bottom: 10px;">Новое имя пользователя</h1>
                    <input type="text" name="full_name" class="form-control" style = "border-bottom-width: 10px; margin-bottom: 10px; padding: .5rem 1rem;
    font-size: 1.25rem; border-radius: .3rem;" value="<?=$_SESSION['user']['full_name']?>" required>
                    <!-- Поле для email -->
                    <h1 class="zag" style = "margin-bottom: 10px;">Новый Email</h1>
                    <input type="email" name="email" style = "border-bottom-width: 10px; margin-bottom: 10px; padding: .5rem 1rem;
    font-size: 1.25rem; border-radius: .3rem;" value="<?=$_SESSION['user']['email']?>" required>
    <ol> </ol> 
    <ol> </ol> 
<h1 class="zag" style = "margin-bottom: 10px;">Старый пароль</h1>
                    <input type="password" name="old_password" style = "border-bottom-width: 10px; margin-bottom: 10px; padding: .5rem 1rem;
    font-size: 1.25rem; border-radius: .3rem;">

    <h1 class="zag" style = "margin-bottom: 10px;">Новый пароль</h1>
                    <input type="password" name="new_password" style = "border-bottom-width: 10px; margin-bottom: 10px; padding: .5rem 1rem;
    font-size: 1.25rem; border-radius: .3rem;">

    <h1 class="zag" style = "margin-bottom: 10px;">Повторите новый пароль</h1>
                    <input type="password" name="confirm_password" style = "border-bottom-width: 10px; margin-bottom: 10px; padding: .5rem 1rem;
    font-size: 1.25rem; border-radius: .3rem;">
                    <ol> </ol>
                    <!-- Кнопка отправки формы -->
                    <button type="submit" class="w-80 btn btn-lg btn-primary">Сохранить изменения</button>
                    <p class="msg">
                        <?php
                            echo $_SESSION['message'];
                            unset($_SESSION['message']); 
                         ?>
                    </p>

                </form>
                <ol> </ol> 
            </main>
        </div>
        <?php include('partials/footer.php'); ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php
    session_start();
    $email_address= !empty($_SESSION['email'])?$_SESSION['email']:'';
    if(!empty($email_address))
    {
        header("location:dashboard.php");
    }

    include('../config/dbConnect.php');
    include('./script/admin-login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin-style.css">
    <title>Панель администрирования</title>
</head>
<body>
    <img class="imgadmin" style="width: 100%;" src="/admin-login/css/Rectangle53.png">
    <form class="box" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h1>Авторизация</h1>
        <div class="form-group">
            <input type="text" name="email" class="form-control" placeholder="Email" value="<?php echo $set_email; ?>">
            <p class="err-msg">
                <?php
                    if ($emailErr!=1) {
                        echo $emailErr;
                    }
                ?>
            </p>
        </div>
        <div class="form-group">
            <input type="password" class="form-control"  placeholder="Пароль" name="password">
            <p class="err-msg">
                <?php
                    if($passErr!=1) {
                        echo $passErr;
                    } 
                ?>
            </p>
        </div>
        <button type="submit" class="button" name="login">Войти</button>
    </form>
</body>
</html>
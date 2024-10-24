<?php
    session_start();
    include('config/dbConnect.php');
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
        <div class="filter">
        </div>
        <div class="library">
                <p class="allbooks">Мои любимые книги здесь</p>

                <div class="wrap2">
                    <div class="search">
                    <input type="text" class="searchTerm" placeholder="Поиск...">
                    <button type="submit" class="searchButton">
                    <i class="fa fa-search"></i>
                    </button>
                    </div>
                </div>

                <?php 
                if ($res -> num_rows > 0) {
                    while ($data = mysqli_fetch_assoc($res)) {
                        array_push($result, $data);
                ?>
                <div class="container-book">
                    <div class="bookimage">
                        <img src=<?php echo "media/bookimages/".$data['bookid'].".jpg" ?> alt="bookimage">
                    </div>
                    <div class="information">
                        <h4><?php echo $data['bookname'] ?></h4>
                        <p>Genre: <?php echo $data['genrename'] ?></p>
                        <p>Author: <?php echo $data['authorname'] ?></p>
                        <br>
                        <br>
                        <br>
                        <br>
                    </div>
                </div>
                <?php
                        }
                    } else {
                ?>
                <p>Упс. Ты ещё не добавил ни одной книги в избранное</p>
                <?php } ?>
            </div>
        </div>
        <?php include('partials/footer.php'); ?>
    </div>

    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
</body>

</html>
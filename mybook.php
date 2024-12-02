<?php
session_start();
include('config/dbConnect.php');

$userId = $_SESSION['user']['id'];
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
                <li> Рекомендации
                </li>
            </div>
            <div class="library">
                <p class="allbooks">Мои любимые книги здесь</p>

                <?php
                $sql = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, ROUND(b.rating, 2) AS rating_2 
                FROM favorites f 
                JOIN book b ON f.book_id = b.id 
                LEFT JOIN author a ON b.authorId = a.id 
                LEFT JOIN genre g ON b.genreId = g.id 
                WHERE f.user_id = ? AND b.flag = 1";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);

                // Выполняем запрос
                $stmt->execute();
                $res = $stmt->get_result();
                
                if ($res->num_rows > 0) {
                    while ($data = $res->fetch_assoc()) {
                            ?>
                        <div class="container-book">
                            <div class="bookimage">
                                <img src=<?php echo htmlspecialchars($data['img']); ?> alt="bookimage">
                            </div>
                            <div class="information">
                                <h4><?php echo $data['bookname'] ?></h4>
                                <p>Genre: <?php echo $data['genrename'] ?></p>
                                <p>Author: <?php echo $data['authorname'] ?></p>
                                <p>Raiting: <?php echo $data['rating_2'] ?></p>
                                <br>
                                <form action="remove_to_favorites.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $data['bookid']; ?>">
                                    <button type="submit" class="favoritebutton" >Удалить из избранного</button>
                                </form>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <p class="msg_null">Упс. Ты ещё не добавил ни одной книги в избранное</p>
                    <img src="media/cat.png" class="nobook_cat">
                <?php } ?>
            </div>
        </div>
        <?php include('partials/footer.php'); ?>
    </div>

    <!-- Модальное окно для уведомления -->
<div id="authModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
        <h4>Необходима авторизация</h4>
        <br>
        <p>Пожалуйста, войдите в свою учетную запись, чтобы добавить книги в избранное.</p>
        <br>
        <button onclick="closeModal()" class="closemodal">Закрыть</button>
        <a href="reg/reg.php" class="autoriz">Авторизоваться</a>
    </div>
</div>

<script>
function showModal() {
    document.getElementById('authModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('authModal').style.display = 'none';
}

// Проверка параметра URL для показа модального окна
const urlParams = new URLSearchParams(window.location.search);
if (!(urlParams.has('auth_required'))) {
    showModal();
}
</script>
    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
</body>

</html>
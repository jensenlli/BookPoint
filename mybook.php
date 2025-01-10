<?php
session_start();
include('config/dbConnect.php');

$userId = $_SESSION['user']['id'];

// Получение рекомендаций от Flask API
// Проверка наличия записей в таблице ratings для данного пользователя
$recommendations = [];
if ($userId) {
    $sql_check = "SELECT COUNT(*) AS count FROM ratings WHERE user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $userId);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $row_check = $res_check->fetch_assoc();

    if ($row_check['count'] > 0) {
        // Получение рекомендаций от Flask API
        $url = "http://127.0.0.1:5000/recommend?user_id=" . $userId . "&n=5"; // Измените на ваш IP, если нужно
        $response = @file_get_contents($url);
        $data = json_decode($response, true);
        
        if (isset($data['recommendations'])) {
            $recommendations = $data['recommendations'];
        }
    } else {
        // Если нет записей в ratings, оставляем рекомендации пустыми
        $recommendations = [];
    }
}
// Получение топ 3 книги по рейтингу
$topBooks = [];
$sql_top_books = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, ROUND(b.rating, 2) AS rating_2 
                  FROM book b 
                  LEFT JOIN author a ON b.authorId = a.id 
                  LEFT JOIN genre g ON b.genreId = g.id 
                  WHERE b.flag = 1 
                  ORDER BY b.rating DESC 
                  LIMIT 3";
$stmt_top_books = $conn->prepare($sql_top_books);
$stmt_top_books->execute();
$res_top_books = $stmt_top_books->get_result();

if ($res_top_books->num_rows > 0) {
    while ($topBook = $res_top_books->fetch_assoc()) {
        $topBooks[] = $topBook;
    }
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
            <?php if ($userId): ?>
                <div class="filter" id="filter">
                    <ul>
                        <li style="padding-left: 20px;">Рекомендации специально для</li>
                        <li style="padding-left: 20px; color: #CC9600;position: absolute;left: 70px;top: 156px;">Вас</li>
                        <ul>

                            <div class="recommended-books">
                            <?php if (!empty($recommendations)): ?>
                                    <?php foreach ($recommendations as $book_id): ?>
                                        <?php
                                        // Получите информацию о книге из базы данных
                                        $sql_book = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, ROUND(b.rating, 2) AS rating_2 
                                                     FROM book b 
                                                     LEFT JOIN author a ON b.authorId = a.id 
                                                     LEFT JOIN genre g ON b.genreId = g.id 
                                                     WHERE b.id = ?";
                                        $stmt_book = $conn->prepare($sql_book);
                                        $stmt_book->bind_param("i", $book_id);
                                        $stmt_book->execute();
                                        $res_book = $stmt_book->get_result();
                                        $book_data = $res_book->fetch_assoc();
                                        ?>
                                        <a href="thisbook.php?id=<?php echo $book_data['bookid']; ?>" style="color:black !important;">
                                            <div class="container-book">
                                                <div class="bookimage_rec">
                                                    <img src="<?php echo htmlspecialchars($book_data['img']); ?>" alt="bookimage_rec" style="width: 110px;">
                                                </div>
                                                <div class="information">
                                                    <h4><?php echo htmlentities($book_data['bookname'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                                    <p>Жанр: <?php echo htmlentities($book_data['genrename'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>Автор: <?php echo htmlentities($book_data['authorname'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>Рейтинг: <?php echo htmlentities($book_data['rating_2'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($topBooks as $topBook): ?>
                                        <a href="thisbook.php?id=<?php echo $topBook['bookid']; ?>" style="color:black !important;">
                                            <div class="container-book">
                                                <div class="bookimage_rec">
                                                    <img src="<?php echo htmlspecialchars($topBook['img']); ?>" alt="topbookimage" style="width: 110px;">
                                                </div>
                                                <div class="information">
                                                    <h4><?php echo htmlentities($topBook['bookname'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                                    <p>Жанр: <?php echo htmlentities($topBook['genrename'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>Автор: <?php echo htmlentities($topBook['authorname'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>Рейтинг: <?php echo htmlentities($topBook['rating_2'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                </div>
            <?php endif; ?>

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
                            <a href="thisbook.php?id=<?php echo $data['bookid']; ?>">
                                <div class="bookimage">
                                    <img src="<?php echo htmlspecialchars($data['img']); ?>" alt="bookimage">
                                </div>
                            </a>
                            <div class="information">
                                <h4><?php echo htmlentities($data['bookname'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p>Жанр: <?php echo htmlentities($data['genrename'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p>Автор: <?php echo htmlentities($data['authorname'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p>Рейтинг: <?php echo htmlentities($data['rating_2'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <br>
                                <form action="remove_to_favorites.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $data['bookid']; ?>">
                                    <button type="submit" class="favoritebutton">Удалить из избранного</button>
                                </form>
                                <br><br><br>
                                <form action="thisbook.php" method="GET">
                                    <input type="hidden" name="id" value="<?php echo $data['bookid']; ?>">
                                    <button type="submit" class="readbutton">Читать</button>
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
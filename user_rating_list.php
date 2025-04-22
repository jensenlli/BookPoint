<?php
session_start();
include('config/dbConnect.php');

$userId = $_SESSION['user']['id'];

// Получение рекомендаций от Flask API
$recommendations = [];
if ($userId) {
    $sql_check = "SELECT COUNT(*) AS count FROM ratings WHERE user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $userId);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $row_check = $res_check->fetch_assoc();

    if ($row_check['count'] > 0) {
        $url = "http://127.0.0.1:5000/recommend?user_id=" . $userId . "&n=5";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['recommendations'])) {
            $recommendations = $data['recommendations'];
        }
    } else {
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
                        <br>
                        <li style="padding-left: 20px; color: #CC9600;position: absolute;top: 156px; left: 50px;">Вас</li>
                        <ul>
                            <div class="recommended-books">
                                <?php if (!empty($recommendations)): ?>
                                    <?php foreach ($recommendations as $book_id): ?>
                                        <?php
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
                        </ul>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="library">
                <p class="allbooks">Мои оцененные книги здесь</p>

                <?php
                $sql = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, f.rating_user 
                FROM ratings f 
                JOIN book b ON f.book_id = b.id 
                LEFT JOIN author a ON b.authorId = a.id 
                LEFT JOIN genre g ON b.genreId = g.id 
                WHERE f.user_id = ? AND b.flag = 1";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
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
                                <p>Моя оценка: <?php echo htmlentities($data['rating_user'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <br>
                                <button class="favoritebutton" onclick="openRatingModal(<?php echo $data['bookid']; ?>, <?php echo $data['rating_user']; ?>)">Изменить оценку</button>
                                <button class="delete-button" onclick="deleteRating(<?php echo $data['bookid']; ?>)">Удалить оценку</button>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <p class="msg_null">Упс. Ты ещё не оценил ни одну книгу</p>
                    <img src="media/cat.png" class="nobook_cat">
                <?php } ?>
            </div>
        </div>

        <div id="ratingModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
            <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:600px; text-align:center;">
                <span class="close">&times;</span>
                <p>Оцените книгу
                <p>
                <div id="ratingButtons">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <button class="rating-button" data-rating="<?php echo $i; ?>"><?php echo $i; ?></button>
                    <?php endfor; ?>
                </div>
                <button id="submitRating" style="display: none; transform: translate(265px, 10px); background-color: unset;">ОK</button>
                <br>
            </div>
        </div>

        <div id="successModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
            <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
                <span class="close">&times;</span>
                <p>Оценка успешно обновлена!</p>
            </div>
        </div>

        <div id="errorModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
            <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
                <span class="close">&times;</span>
                <p>Произошла ошибка при отправке оценки. Повторите позднее</p>
            </div>
        </div>

        <?php include('partials/footer.php'); ?>
    </div>

    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>

    <script>
        let selectedRating;
        let successModal = document.getElementById('successModal'); // Получаем элемент модального окна успеха
        let errorModal = document.getElementById('errorModal'); // Получаем элемент модального окна ошибки

        // Закрыть модальное окно при клике вне его
        window.onclick = function(event) {
            if (event.target == ratingModal) {
                ratingModal.style.display = "none";
            }
            if (event.target == successModal) {
                successModal.style.display = "none";
            }
            if (event.target == errorModal) {
                errorModal.style.display = "none";
            }
        }

        function openRatingModal(bookId, currentRating) {
            const ratingModal = document.getElementById('ratingModal');
            const submitRatingButton = document.getElementById('submitRating');
            const ratingButtons = document.querySelectorAll('.rating-button');

            // Сброс предыдущего состояния
            ratingButtons.forEach(button => {
                button.classList.remove('selected'); // Удаляем выделение
            });
            submitRatingButton.style.display = "none"; // Скрываем кнопку "Оценить"

            // Устанавливаем текущую оценку
            if (currentRating) {
                const selectedButton = document.querySelector(`.rating-button[data-rating="${currentRating}"]`);
                if (selectedButton) {
                    selectedButton.classList.add('selected'); // Выделяем текущую оценку
                    selectedRating = currentRating; // Присваиваем значение
                    submitRatingButton.style.display = "block"; // Показываем кнопку "Оценить"
                }
            }

            // Добавляем обработчик событий для кнопок рейтинга
            ratingButtons.forEach(button => {
                button.onclick = function() {
                    selectedRating = this.getAttribute('data-rating'); // Получаем рейтинг из атрибута
                    ratingButtons.forEach(btn => btn.classList.remove('selected')); // Удаляем выделение
                    this.classList.add('selected'); // Выделяем выбранную кнопку
                    submitRatingButton.style.display = "block"; // Показываем кнопку "Оценить"
                };
            });

            // Показать модальное окно
            ratingModal.style.display = "block";

            // Обработчик для отправки рейтинга
            submitRatingButton.onclick = function() {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'submit-rating.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Обработка успешного ответа
                        successModal.style.display = "block"; // Показываем модальное окно успеха
                        ratingModal.style.display = "none"; // Закрываем модальное окно рейтинга

                        // Перезагружаем страницу через 3 секунды
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        errorModal.style.display = "block"; // Показываем модальное окно ошибки
                    }
                };
                xhr.send('book_id=' + bookId + '&rating=' + selectedRating + '&user_id=<?php echo $userId; ?>');
            };
        }

        function deleteRating(bookId) {
            if (confirm("Вы уверены, что хотите удалить оценку?")) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete-rating.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Обработка успешного ответа
                        alert("Оценка успешно удалена!");
                        location.reload(); // Перезагружаем страницу для обновления данных
                    } else {
                        alert("Произошла ошибка при удалении оценки. Попробуйте снова.");
                    }
                };
                xhr.send('book_id=' + bookId + '&user_id=<?php echo $userId; ?>');
            }
        }
    </script>
</body>

</html>
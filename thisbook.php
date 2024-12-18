<?php
session_start();
include('config/dbConnect.php');
$userId = $_SESSION['user']['id'];

// Проверяем, что ID книги передан
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Получаем ID книги из GET-параметров
$id = intval($_GET['id']);

// Получаем данные книги
$sql = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, b.ISBN, ROUND(b.rating, 2) AS rating_2, b.yearPub, b.Publisher FROM book AS b
LEFT JOIN author AS a ON b.authorId = a.id
LEFT JOIN genre AS g ON b.genreId = g.id WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$result1 = $stmt->execute();

if (!$result1) {
    echo "Ошибка при получении данных книги: " . $conn->error;
    exit;
}

$row = $stmt->get_result()->fetch_assoc();

// Проверяем, есть ли такая книга
if (!$row) {
    echo "Книга не найдена.";
    exit;
}

// Получаем текст книги
$sql = "SELECT text FROM textbook WHERE bookid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$result2 = $stmt->execute();

if (!$result2) {
    echo "Ошибка при получении текста книги: " . $conn->error;
    exit;
}

$text = $stmt->get_result()->fetch_assoc()['text'];

// Закрываем соединение со второй таблицей
$stmt->close();
$conn->close();

// Параметры пагинации
$chars_per_page = 15000; // Количество символов на страницу
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Текущая страница
$total_chars = strlen($text); // Общая длина текста
$total_pages = ceil($total_chars / $chars_per_page); // Общее количество страниц

// Корректировка номера страницы
if ($page < 1) {
    $page = 1; // Устанавливаем минимум
} elseif ($page > $total_pages) {
    $page = $total_pages; // Устанавливаем максимум
}

// Определяем, какой текст показывать
$offset = ($page - 1) * $chars_per_page; // Текущий начальный символ

// Пропускаем переносы строк, если это необходимо
while ($offset < $total_chars && in_array($text[$offset], ["\r", "\n", "\br"])) {
    $offset++;
}

$page_text = substr($text, $offset, $chars_per_page); // Получаем текст для текущей страницы

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlentities($row['bookname'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="partials/css/footer.css">
    <link rel="stylesheet" href="partials/css/header.css">
</head>

<body>
    <?php include('header.php'); ?>
    <div class="container mt-5">
        <div class="card">
            <img src="<?php echo htmlentities($row['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlentities($row['bookname'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top">
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlentities($row['bookname'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p><strong>Автор:</strong> <?php echo htmlentities($row['authorname'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Жанр:</strong> <?php echo htmlentities($row['genrename'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Рейтинг:</strong> <?php echo htmlentities($row['rating_2'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Год публикации:</strong> <?php echo htmlentities($row['yearPub'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Издательство:</strong> <?php echo htmlentities($row['Publisher'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>IBSN:</strong> <?php echo htmlentities($row['ISBN'], ENT_QUOTES, 'UTF-8'); ?></p>
                <br>
                <form method="GET" action="thisbook.php">
                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                    <input type="number" class="inputpages" name="page" min="1" max="<?php echo $total_pages; ?>" placeholder="Введите номер страницы" required />
                    <button type="submit" class="inputpagesbtn">Перейти</button>
                </form>
                <br><br>
                <?php
                // Проверяем, что у нас есть текст для отображения
                if (!empty(trim($page_text))) {
                    // Заменяем переносы строк на <br> после проверки на пустоту
                    echo nl2br(htmlspecialchars($page_text, ENT_QUOTES, 'cp1251'));
                } else {
                    echo "Текст книги пока что не доступен. Попробуйте зайти немного позднее";
                }
                ?>
                <br><br><br><br>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a style="color:#CC9600; padding-bottom: 50px;" href="thisbook.php?id=<?php echo $id; ?>&page=<?php echo $page - 1; ?>">« Предыдущая</a>
                    <?php endif; ?>

                    <span style="padding: 0 15px; ">Страница <?php echo $page; ?> из <?php echo $total_pages; ?></span>

                    <?php if ($page < $total_pages): ?>
                        <a style="color:#CC9600; padding-bottom: 50px;" href="thisbook.php?id=<?php echo $id; ?>&page=<?php echo $page + 1; ?>">Следующая »</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <!-- Модальное окно для оценки книги -->
    <div id="ratingModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
        <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:600px; text-align:center;">
            <span class="close">&times;</span>
            <h1>Оцените книгу</h1>
            <div id="ratingButtons">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <button class="rating-button" data-rating="<?php echo $i; ?>"><?php echo $i; ?></button>
                <?php endfor; ?>
            </div>
            <button id="submitRating" style="display: none; transform: translate(265px, 10px); background-color: unset;">ОK</button>
            <br></br>
        </div>
    </div>
    <!-- Модальное окно для успешного сообщения -->
    <div id="successModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
        <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
            <span class="close-success">&times;</span>
            <h2>Спасибо за вашу оценку!</h2>
            <div class="box_thanks"></div>
        </div>
    </div>

    <!-- Модальное окно для сообщения об ошибке -->
    <div id="errorModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
        <div class="modal-content" style="background-color:white; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
            <span class="close-error">&times;</span>
            <h2>Произошла ошибка. Попробуйте еще раз.</h2>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingModal = document.getElementById('ratingModal');
            const successModal = document.getElementById('successModal');
            const errorModal = document.getElementById('errorModal');
            const closeSuccess = document.getElementsByClassName('close-success')[0];
            const closeError = document.getElementsByClassName('close-error')[0];
            const ratingButtons = document.querySelectorAll('.rating-button');
            const submitRatingButton = document.getElementById('submitRating');
            let selectedRating = null;

            // Открыть модальное окно, если пользователь на 15-й странице
            if (<?php echo $page; ?> === 15) {
                ratingModal.style.display = "block";
            }

            // Закрыть модальное окно для рейтинга
            closeSuccess.onclick = function() {
                successModal.style.display = "none";
            }

            closeError.onclick = function() {
                errorModal.style.display = "none";
            }

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

            // Обработчик для кнопок рейтинга
            ratingButtons.forEach(button => {
                button.addEventListener('click', function() {
                    selectedRating = this.getAttribute('data-rating');
                    submitRatingButton.style.display = "block"; // Показываем кнопку "Оценить"
                });
            });

            // Обработчик для отправки рейтинга
            submitRatingButton.addEventListener('click', function() {
                if (selectedRating) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'submit-rating.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            successModal.style.display = "block"; // Показываем модальное окно успеха
                            ratingModal.style.display = "none"; // Закрываем модальное окно рейтинга
                        } else {
                            errorModal.style.display = "block"; // Показываем модальное окно ошибки
                        }
                    };
                    xhr.send('book_id=<?php echo $id; ?>&rating=' + selectedRating + '&user_id=<?php echo $userId; ?>');
                }
            });
        });
    </script>
</body>

</html>
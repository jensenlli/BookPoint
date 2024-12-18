<?php
session_start();
include('config/dbConnect.php');

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
                <input type="number" class = "inputpages" name="page" min="1" max="<?php echo $total_pages; ?>" placeholder="Введите номер страницы" required />
                <button type="submit" class = "inputpagesbtn">Перейти</button>
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
</body>
</html>
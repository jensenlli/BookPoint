<?php
session_start();
include('config/dbConnect.php');

$limit = 10;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="partials/css/footer.css">
    <link rel="stylesheet" href="partials/css/header.css">
    <title>BookPoint</title>
</head>

<body>
    <div class="wrapper">
        <?php include('header.php'); ?>
        <div class="page">
            <div class="filter">
                <form name="form" method="get">
                    <ul>
                        <li>Жанры
                            <ul>
                                <?php

                                // Ассоциативный массив для сопоставления жанров
                                $genreTranslations = [
                                    'Psychology' => 'Психология',
                                    'Thriller' => 'Триллер',
                                    'Detective' => 'Детектив',
                                    'Novel' => 'Роман',
                                    'Historycal' => 'Историческое',
                                    'Faniastics' => 'Фантастика',
                                    'Comedy' => 'Комедия',
                                    'Prose' => 'Проза',
                                    'Documentary' => 'Документальное',
                                    'Classics' => 'Классика',
                                    'Drama' => 'Драма'
                                ];

                                // Получение жанров
                                $sqlgenres = "SELECT * FROM genre WHERE flag = 1 ORDER BY name";
                                $res = $conn->query($sqlgenres);
                                if (!$res) echo mysqli_error($conn);

                                while ($data = mysqli_fetch_assoc($res)) {
                                    $englishName = $data['name'];
                                    $russianName = isset($genreTranslations[$englishName]) ? $genreTranslations[$englishName] : $englishName; // Получаем русское название
                                ?>
                                    <li>
                                        <input type="checkbox" name="genres[]" value="<?php echo $data['id']; ?>" id="genre-<?php echo $data['id']; ?>" <?php if (isset($_GET['genres']) && in_array($data['id'], $_GET['genres'])) echo "checked"; ?>>
                                        <label for="genre-<?php echo $data['id']; ?>"><?php echo $russianName; ?></label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                    <input type="submit" value="Фильтровать" name="filter" class="filterbutton">
                </form>

                <?php
                function addFilterCondition($where, $add, $and = true)
                {
                    if ($where) {
                        if ($and) $where .= " AND $add";
                        else $where .= " OR $add";
                    } else {
                        $where = $add;
                    }
                    return $where;
                }

                $where = "";
                if (!empty($_GET["filter"])) {
                    // Фильтрация по жанрам
                    if (!empty($_GET["genres"])) {
                        $idsgenres = $_GET["genres"];
                        $genrestring = implode(", ", array_map('intval', $idsgenres)); // Приводим к целым числам
                        $where = addFilterCondition($where, 'g.id IN (' . $genrestring . ')');
                    }

                    // Фильтрация по авторам
                    if (!empty($_GET["authors"])) {
                        $idauthors = $_GET["authors"];
                        $authorstring = implode(", ", array_map('intval', $idauthors)); // Приводим к целым числам
                        $where = addFilterCondition($where, 'a.id IN (' . $authorstring . ')');
                    }
                }

                // Основной SQL-запрос
                $sql = 'SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, b.rating FROM book AS b
                                        LEFT JOIN author AS a ON b.authorId = a.id
                                        LEFT JOIN genre AS g ON b.genreId = g.id
                                        WHERE b.flag = 1';

                if ($where) {
                    $sql .= " AND $where"; // Добавляем условия фильтрации
                }

                $res = $conn->query($sql);
                if (!$res) echo mysqli_error($conn);
                $result = array();
                ?>
            </div>
            <div class="library">
                <p class="allbooks">Все книги здесь</p>

                <div class="wrap2">
                    <div class="search">
                        <form method="GET" action="" style="width: 600px;">
                            <input type="text" name="searchTerm" class="searchTerm" placeholder="Поиск..." value="<?php echo isset($_GET['searchTerm']) ? htmlspecialchars($_GET['searchTerm']) : ''; ?>">
                            <button type="submit" class="searchButton">
                                <i class="fa fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <?php
                // Получение текущей страницы из URL, если она не задана, устанавливаем 1
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Получение значения поиска из параметров URL
                $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

                // Подготовка условий для поиска и фильтрации
                $searchCondition = 'WHERE b.flag = 1';
                if (!empty($searchTerm)) {
                    $searchTerm = $conn->real_escape_string($searchTerm); // Экранирование для защиты от SQL-инъекций
                    $searchCondition .= " AND (b.name LIKE '%$searchTerm%' OR a.name LIKE '%$searchTerm%')";
                }

                // Фильтрация по жанрам
                $where = "";
                if (!empty($_GET["genres"])) {
                    $idsgenres = $_GET["genres"];
                    $genrestring = implode(", ", array_map('intval', $idsgenres)); // Приводим к целым числам
                    $where = addFilterCondition($where, 'g.id IN (' . $genrestring . ')');
                }

                // Объединяем условия поиска и фильтрации
                if ($where) {
                    $searchCondition .= " AND $where";
                }

                // Получение общего количества записей
                $countQuery = "SELECT COUNT(*) as total FROM book AS b 
                LEFT JOIN author AS a ON b.authorId = a.id 
                LEFT JOIN genre AS g ON b.genreId = g.id $searchCondition";
                $countResult = $conn->query($countQuery);
                $totalCount = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalCount / $limit);

                // Запрос с ограничением по количеству записей
                $sql = "SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img, ROUND(b.rating, 2) AS rating_2 FROM book AS b
        LEFT JOIN author AS a ON b.authorId = a.id
        LEFT JOIN genre AS g ON b.genreId = g.id
        $searchCondition
        LIMIT " . intval($offset) . ", " . intval($limit);

                if (isset($_GET['id'])) {
                    header("Location: thisbook.php?id=" . intval($_GET['id']));
                    exit;
                }

                $res = $conn->query($sql);

                if ($res->num_rows > 0) {
                    while ($data = mysqli_fetch_assoc($res)) {
                        array_push($result, $data);
                ?>
                        
                        <div class="container-book">
                            <a href = "thisbook.php?id=<?php echo $data['bookid']; ?>">
                            <div class="bookimage" style ="min-height: 280px;">
                                <img src=<?php echo htmlspecialchars($data['img']); ?> alt="bookimage">
                            </div>
                            </a>
                            <div class="information">
                                <h4><?php echo $data['bookname'] ?></h4>
                                <p>Жанр: <?php echo $data['genrename'] ?></p>
                                <p>Автор: <?php echo $data['authorname'] ?></p>
                                <p>Рейтинг: <?php echo $data['rating_2'] ?></p>
                                <br>
                                <form action="add_to_favorites.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $data['bookid']; ?>">
                                    <button type="submit" class="favoritebutton">Добавить в избранное</button>
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
                    <p>Не найдены книги по вашему запросу.</p>
                <?php }

                // Пагинация
                echo '<div class="pagination">';
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $page) {
                        echo '<strong>' . $i . '</strong> '; // Текущая страница
                    } else {
                        echo '<a class="pagestr" href="?page=' . $i . '">' . $i . '</a> '; // Ссылка на другую страницу
                    }
                }
                echo '</div>';
                ?>
            </div>
        </div>
        <?php include('partials/footer.php'); ?>
    </div>
    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
</body>

</html>
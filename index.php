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
                                if (isset($_GET['authors'])) {
                                    $authortogenre = "";
                                    foreach ($_GET['authors'] as $n => $row) {
                                        $authortogenre .= $row;
                                        if ($n < count($_GET['authors']) - 1) {
                                            $authortogenre .= ", ";
                                        }
                                    }
                                    $sqlgenres = "  SELECT    g.name as name,
                                                                g.id as id
                                                        FROM genre as g
                                                        WHERE g.id in (
                                                            SELECT b.genreId
                                                            FROM book as b
                                                            WHERE b.authorId in (" . $authortogenre . ")
                                                        )";
                                } else {
                                    $sqlgenres = "SELECT * FROM genre WHERE flag = 1 ORDER BY name";
                                }
                                $res = $conn->query($sqlgenres);
                                if (!$res) echo mysqli_error($conn);
                                while ($data = mysqli_fetch_assoc($res)) {
                                ?>
                                    <li><input type="checkbox" name="genres[]" value="<?php echo $data['id']; ?>" id="genre-<?php echo $data['id']; ?>" <?php if (isset($_GET['genres'])) {
                                                                                                                                                            if (in_array($data['id'], $_GET['genres'])) echo "checked";
                                                                                                                                                        } ?>><label for="genre-<?php echo $data['id']; ?>"><?php echo $data['name'] ?></label></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li>Автор
                            <ul>
                                <?php
                                if (isset($_GET['genres'])) {
                                    $genretoauthor = "";
                                    foreach ($_GET['genres'] as $n => $row) {
                                        $genretoauthor .= $row;
                                        if ($n < count($_GET['genres']) - 1) {
                                            $genretoauthor .= ", ";
                                        }
                                    }
                                    $sqlauthors = "SELECT a.name as name, 
                                                              a.id as id 
                                                       FROM author as a 
                                                       where a.id in (
                                                           select b.authorId 
                                                           from book as b 
                                                           where b.genreId in (" . $genretoauthor . ")
                                                        )";
                                } else {
                                    $sqlauthors = "SELECT * FROM author WHERE flag = 1 ORDER BY name";
                                }
                                $res = $conn->query($sqlauthors);
                                if (!$res) echo mysqli_error($conn);
                                while ($data = mysqli_fetch_assoc($res)) {
                                ?>
                                    <li><input type="checkbox" name="authors[]" value="<?php echo $data['id']; ?>" id="author-<?php echo $data['id'] ?>" <?php if (isset($_GET['authors'])) {
                                                                                                                                                                if (in_array($data['id'], $_GET['authors'])) echo "checked";
                                                                                                                                                            }
                                                                                                                                                            ?>><label for="author-<?php echo $data['id']; ?>"><?php echo $data['name']; ?></label></li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                    <input type="submit" value="Filter!" name="filter" class="filterbutton">
                </form>
                <?php
                function addFilterCondition($where, $add, $and = true)
                {
                    if ($where) {
                        if ($and) $where .= " AND $add";
                        else $where .= " OR $add";
                    } else $where = $add;
                    return $where;
                }

                if (!empty($_GET["filter"])) {
                    $where = "";
                    if ($_GET["genres"]) {
                        $idsgenres = $_GET["genres"];
                        $genrestring = "";
                        foreach ($idsgenres as $n => $row) {
                            $genrestring .= $row;
                            if ($n < count($idsgenres) - 1) {
                                $genrestring .= ", ";
                            }
                        }
                        $where = addFilterCondition($where, 'g.id IN (' . $genrestring . ')');
                    }
                    if ($_GET["authors"]) {
                        $idauthors = $_GET["authors"];
                        $authorstring = "";
                        foreach ($idauthors as $n => $row) {
                            $authorstring .= $row;
                            if ($n < count($idauthors) - 1) {
                                $authorstring .= ", ";
                            }
                        }
                        $where = addFilterCondition($where, 'a.id IN (' . $authorstring . ')');
                    }
                }
                $sql = 'SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img FROM (
                            SELECT id, name, genreId, authorId, img
                            FROM book WHERE flag = 1
                        ) AS b
                        LEFT JOIN author AS a ON b.authorId = a.id
                        LEFT JOIN genre AS g ON b.genreId = g.id';

                if ($where) $sql .= " WHERE $where";
                $res = $conn->query($sql);
                $result = array();
                ?>
            </div>
            <div class="library">
                <p class="allbooks">Все книги здесь</p>

                <div class="wrap2">
                    <div class="search">
                        <input type="text" class="searchTerm" placeholder="Поиск...">
                        <button type="submit" class="searchButton">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>

                <?php
                // Получение текущей страницы из URL, если она не задана, устанавливаем 1
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Получение общего количества записей
                $countQuery = "SELECT COUNT(*) as total FROM book";
                $countResult = $conn->query($countQuery);
                $totalCount = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalCount / $limit);

                // Запрос с ограничением по количеству записей
                $sql = 'SELECT b.id AS bookid, b.name AS bookname, a.name AS authorname, g.name AS genrename, b.img FROM (
                            SELECT id, name, genreId, authorId, img
                            FROM book WHERE flag = 1
                        ) AS b
                        LEFT JOIN author AS a ON b.authorId = a.id
                        LEFT JOIN genre AS g ON b.genreId = g.id
                        LIMIT ' . intval($offset) . ', ' . intval($limit);

                $res = $conn->query($sql);

                if ($res->num_rows > 0) {
                    while ($data = mysqli_fetch_assoc($res)) {
                        array_push($result, $data);

                ?>
                        <div class="container-book">
                            <div class="bookimage">
                                <img src=<?php echo htmlspecialchars($data['img']); ?> alt="bookimage">
                            </div>
                            <div class="information">
                                <h4><?php echo $data['bookname'] ?></h4>
                                <p>Genre: <?php echo $data['genrename'] ?></p>
                                <p>Author: <?php echo $data['authorname'] ?></p>
                                <br>
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
                        echo '<a href="?page=' . $i . '">' . $i . '</a> '; // Ссылка на другую страницу
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
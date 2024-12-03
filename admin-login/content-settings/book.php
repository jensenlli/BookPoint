<?php
session_start();
$email_address = $_SESSION['email'];
include('../../config/dbConnect.php');
if (empty($email_address)) {
    header("Location: digitalbooks/admin-panel/index.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).on('click', '.delete', function(e) {
            var el = $(this);
            var id = $(this).attr('id');
            var name = $(this).attr('name');
            $.ajax({
                type: "GET",
                url: "buffer.php",
                data: {
                    deleteId: id,
                    deleteData: name
                },
                dataType: "html",
                success: function(data) {
                    alert('deleted!');
                }
            })
        });

        $(document).on('click', '.restore', function(e) {
            var el = $(this);
            var id = $(this).attr('id');
            var name = $(this).attr('name');
            $.ajax({
                type: "GET",
                url: "buffer.php",
                data: {
                    restoreId: id,
                    restoreData: name
                },
                dataType: "html",
                success: function(data) {
                    alert('restored!');
                }
            })
        });

        $(document).on('click', '.full-delete', function(e) {
            var el = $(this);
            var id = $(this).attr('id');
            var name = $(this).attr('name');
            $.ajax({
                type: "GET",
                url: "buffer.php",
                data: {
                    fullDeleteId: id,
                    fullDeleteData: name
                },
                dataType: "html",
                success: function(data) {
                    alert("Full deleted!");
                }
            })
        });
    </script>
</head>

<body>
    <div class="wrapper">
        <?php
        include('../partials/header.php');
        ?>
        <div class="page">
            <?php
            include('../partials/sidebar.php');
            ?>
            <div class="ves-action" style="position: absolute;top: 250px;">
                <?php
                if ($_GET['cat'] == 'add-book') {
                    if (!empty($_GET['edit'])) {
                        $editId = $_GET['edit'];
                        $query = "SELECT * FROM book WHERE id=$editId";
                        $res = $conn->query($query);
                        $editData = mysqli_fetch_assoc($res);
                        $name = $editData['name'];
                        $authorId = $editData['authorId'];
                        $genreId = $editData['genreId'];
                        $img = $editData['img'];
                        $yearPub = $editData['yearPub'];
                        $pub = $editData['Publisher'];
                        $isbn = $editData['ISBN'];
                        $rating = $editData['rating'];

                        $idAttr = "updateBookForm";
                    } else {
                        $name = "";
                        $authorId = "";
                        $genreId = "";
                        $img = "";
                        $yearPub = "";
                        $pub = "";
                        $isbn = "";
                        $rating = "";

                        $editId = "";
                        $idAttr = "bookForm";
                    }
                ?>

                    <?php
                    if (isset($_POST['save'])) {
                        if (empty($_GET['edit'])) {
                            $sqlauthor = "SELECT id FROM author WHERE name='" . $_POST['authorId'] . "'";
                            $resauthor = $conn->query($sqlauthor);
                            $dataauthor = mysqli_fetch_assoc($resauthor);
                            $sqlgenre = "SELECT id FROM genre WHERE name='" . $_POST['genreId'] . "'";
                            $resgenre = $conn->query($sqlgenre);
                            $datagenre = mysqli_fetch_assoc($resgenre);
                            $sql = "INSERT INTO book (name,authorId,genreId,rating,ISBN,yearPub,Publisher,img) VALUES (?,?,?,?,?,?,?,?)";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "siidiiss", $_POST['name'], $dataauthor['id'], $datagenre['id'], $_POST['rating'], $_POST['ISBN'], $_POST['yearPub'], $_POST['Publisher'], $_POST['img']);
                            mysqli_stmt_execute($stmt);
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Add book', 'Add book " . $_POST['name'] . "')";
                            $stmt = mysqli_prepare($conn, $sqlTransaction);
                            $stmt->execute();
                        } else {
                            $sqlauthor = "SELECT id FROM author WHERE name='" . $_POST['authorId'] . "'";
                            $resauthor = $conn->query($sqlauthor);
                            $dataauthor = mysqli_fetch_assoc($resauthor);
                            $sqlgenre = "SELECT id FROM genre WHERE name='" . $_POST['genreId'] . "'";
                            $resgenre = $conn->query($sqlgenre);
                            $datagenre = mysqli_fetch_assoc($resgenre);
                            $sql = "UPDATE book SET name=?, authorId=?, genreId=?,rating=?,ISBN=?,yearPub=?,Publisher=?,img=? WHERE id=" . $_GET['edit'];
                            $stmt = mysqli_prepare($conn, $sql);
                            $stmt->bind_param("siidiiss", $_POST['name'], $dataauthor['id'], $datagenre['id'], $_POST['rating'], $_POST['ISBN'], $_POST['yearPub'], $_POST['Publisher'], $_POST['img']);
                            $stmt->execute();
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Edit book', 'Edit book " . $_POST['name'] . "')";
                            $stmt = mysqli_prepare($conn, $sqlTransaction);
                            $stmt->execute();
                        }
                    }
                    ?>
                    <div class="hheader">
                        <?php
                        if (empty($_GET['edit'])) {
                            echo "<h4 style='margin-left: 625px;'>Добавление книги</h4>";
                        } else {
                            echo "<h4 style='margin-left: 600px;'>Редактирование книги</h4>";
                        }
                        ?>
                        <a href="book.php"><button class="right">Перейти к обзору книг</button></a>
                    </div>
                    <div class="view">
                        <form id="<?php echo $idAttr; ?>" rel="<?php echo $editId; ?>" name="book_profile" method="POST">
                            <div class="row">
                                <div class="containr">
                                    <input type="text" placeholder="Название книги" name="name" value="<?php echo $name; ?>" required>
                                </div>
                                <div class="containr">
                                    <input type="text" id="author" placeholder='Автор' name="authorId" value="<?php
                                                                                                                if (!empty($authorId)) {
                                                                                                                    $sqlauthor = "SELECT name FROM author WHERE id=" . $authorId . " AND flag = 1";
                                                                                                                    $resauthor = mysqli_query($conn, $sqlauthor);
                                                                                                                    $dataauthor = mysqli_fetch_assoc($resauthor);
                                                                                                                    echo $dataauthor['name'];
                                                                                                                }
                                                                                                                ?>" required>
                                    <div class="drop-content author" id="myDropdownAuthor">
                                        <?php
                                        $sql = "SELECT name FROM author WHERE flag=1 ORDER BY name";
                                        $res = $conn->query($sql);
                                        if (mysqli_fetch_assoc($res))
                                            while ($data = mysqli_fetch_assoc($res)) {
                                                echo "<a href='#' onclick='enter1(this)' id='absolute'>" . $data['name'] . "</a>";
                                            }
                                        else {
                                            echo mysqli_error($conn);
                                        }
                                        ?>
                                    </div>
                                    <script>
                                        document.body.addEventListener("click", function() {
                                            var evt = window.event || evt;
                                            var obj = evt.target.id;
                                            if (obj == "author") {
                                                if (document.getElementById("myDropdownAuthor").classList.toggle("show") == false)
                                                    document.getElementById("myDropdownAuthor").classList.toggle("show");
                                            } else {
                                                if (document.getElementById("myDropdownAuthor").classList.toggle("show") == true)
                                                    document.getElementById("myDropdownAuthor").classList.toggle("show");
                                            }
                                        }, true);

                                        var input1 = document.getElementById("author");
                                        input1.addEventListener("keyup", function() {
                                            var filter = input1.value.toUpperCase();
                                            var div = document.getElementById("myDropdownAuthor");
                                            a = div.getElementsByTagName("a");
                                            for (i = 0; i < a.length; i++) {
                                                textValue = a[i].textContent || a[i].innerText;
                                                if (textValue.toUpperCase().indexOf(filter) > -1) {
                                                    a[i].style.display = '';
                                                } else {
                                                    a[i].style.display = 'none';
                                                }
                                            }
                                        });

                                        function enter1(fr) {
                                            id = document.getElementById("author");
                                            id.value = fr.textContent;
                                            if (document.getElementById("myDropdownAuthor").classList.toggle("show") == true)
                                                document.getElementById("myDropdownAuthor").classList.toggle("show") == true;
                                        }
                                    </script>
                                </div>
                                <div class="containr">
                                    <input type="text" id="genre" placeholder="Жанр" name="genreId" value="<?php
                                                                                                            if (!empty($genreId)) {
                                                                                                                $sqlgenre = "SELECT name FROM genre WHERE id=" . $genreId . " AND flag = 1";
                                                                                                                $resgenre = $conn->query($sqlgenre);
                                                                                                                $datagenre = mysqli_fetch_assoc($resgenre);
                                                                                                                echo $datagenre['name'];
                                                                                                            }
                                                                                                            ?>" required>
                                    <div class="drop-content genre" id="myDropdownGenre">
                                        <?php
                                        $sql = "SELECT name FROM genre WHERE flag = 1  ORDER BY name";
                                        $res = $conn->query($sql);
                                        if (mysqli_fetch_assoc($res))
                                            while ($data = mysqli_fetch_assoc($res)) {
                                                echo "<a href='#' onclick='enter2(this)' id='absolute'>" . $data['name'] . "</a>";
                                            }
                                        else{
                                            echo mysqli_error($conn);
                                        }
                                        ?>
                                    </div>
                                    <script>
                                        document.body.addEventListener("click", function() {
                                            var evt = window.event || evt;
                                            var obj = evt.target.id;
                                            if (obj == "genre") {
                                                if (document.getElementById("myDropdownGenre").classList.toggle("show") == false)
                                                    document.getElementById("myDropdownGenre").classList.toggle("show");
                                            } else {
                                                if (document.getElementById("myDropdownGenre").classList.toggle("show") == true)
                                                    document.getElementById("myDropdownGenre").classList.toggle("show");
                                            }
                                        }, true);

                                        var input2 = document.getElementById("genre");
                                        input2.addEventListener("keyup", function() {
                                            var filter = input2.value.toUpperCase();
                                            var div = document.getElementById("myDropdownGenre");
                                            a = div.getElementsByTagName("a");
                                            for (i = 0; i < a.length; i++) {
                                                textValue = a[i].textContent || a[i].innerText;
                                                if (textValue.toUpperCase().indexOf(filter) > -1) {
                                                    a[i].style.display = '';
                                                } else {
                                                    a[i].style.display = 'none';
                                                }
                                            }
                                        });

                                        function enter2(fr) {
                                            id = document.getElementById("genre");
                                            id.value = fr.textContent;
                                            if (document.getElementById("myDropdownGenre").classList.toggle("show") == true)
                                                document.getElementById("myDropdownGenre").classList.toggle("show") == true;
                                        }
                                    </script>
                                </div>
                            </div>
                            <div class="row">
                                <div class="containr">
                                    <input type="text" placeholder="Год" name="yearPub" value="<?php echo $yearPub ?>" required>
                                </div>
                                <div class="containr">
                                    <input type="text" placeholder="Pub" name="Publisher" value="<?php echo $pub ?>" required>
                                </div>
                                <div class="containr">
                                    <input type="text" placeholder="Рейтинг" name="rating" value="<?php echo $rating ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="containr">
                                    <input type="text" placeholder="ISBN" name="ISBN" value="<?php echo $isbn ?>" required>
                                </div>
                                <div class="containr">
                                    <input type="text" placeholder="Картинка" name="img" value="<?php echo $img ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="submit-button" name="save">Сохранить</button>
                        </form>
                    </div>
                <?php  } else { ?>
                    <div class="hheader">
                        <h4>Управление книгами</h4>
                        <a href="book.php?cat=add-book"><button class="right">Добавить книгу</button></a>
                    </div>
                    <div class="view">
                        <table>
                            <tr>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Жанр</th>
                                <th>Рейтинг</th>
                                <th>Год</th>
                                <th>Pub</th>
                                <th>ISBN</th>
                                <th>Картинка</th>
                                <th></th>

                                <th>Редактировать</th>
                                <th>Удалить</th>
                            </tr>
                            <?php
                            $sql = "SELECT * FROM book WHERE flag = 1 ORDER BY id";
                            $res = $conn->query($sql);
                            if ($res->num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                            ?>
                                    <tr>
                                        <td><?php echo $data['name']; ?></td>
                                        <td><?php
                                            $sqlauthor = "SELECT name FROM author WHERE id=" . $data['authorId'];
                                            $resauthor = mysqli_query($conn, $sqlauthor);
                                            while ($dataauthor = mysqli_fetch_assoc($resauthor)) {
                                                echo $dataauthor['name'];
                                            }
                                            ?></td>
                                        <td><?php
                                            $sqlgenre = "SELECT name FROM genre WHERE id=" . $data['genreId'];
                                            $resgenre = $conn->query($sqlgenre);
                                            while ($datagenre = mysqli_fetch_assoc($resgenre)) {
                                                echo $datagenre['name'];
                                            }
                                            ?></td>
                                        <td><?php echo ROUND($data['rating'], 2); ?></td>
                                        <td><?php echo $data['yearPub']; ?></td>
                                        <td><?php echo $data['Publisher']; ?></td>
                                        <td><?php echo $data['ISBN']; ?></td>
                                        <td><?php echo $data['img']; ?></td>
                                        <td></td>
                                        <td><a href="book.php?cat=add-book&edit=<?php echo $data['id']; ?>"><i class="far fa-edit"></i></a></td>
                                        <td><a href="javascript:void(0)" class="delete" name="delete-book" id="<?php echo $data['id']; ?>"><i class="far fa-trash-alt"></i></a></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">Нет данных о существующих книгах.</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>
                    <br>
                    <div class="hheader">
                        <h4>Удаленные книги</h4>
                    </div>
                    <div class="view">
                        <table>
                            <tr>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Жанр</th>
                                <th>Рейтинг</th>
                                <th>Год</th>
                                <th>Pub</th>
                                <th>ISBN</th>
                                <th>Картинка</th>
                                <th></th>
                                <th>Удалить</th>
                                <th>Восстановить</th>
                            </tr>
                            <?php
                            $sql = "SELECT * FROM book WHERE flag <> 1 ORDER BY id";
                            $res = $conn->query($sql);
                            if ($res->num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                            ?>
                                    <tr>
                                        <td><?php echo $data['name']; ?></td>
                                        <td><?php
                                            $sqlauthor = "SELECT name FROM author WHERE id=" . $data['authorId'];
                                            $resauthor = mysqli_query($conn, $sqlauthor);
                                            while ($dataauthor = mysqli_fetch_assoc($resauthor)) {
                                                echo $dataauthor['name'];
                                            }
                                            ?></td>
                                        <td><?php
                                            $sqlgenre = "SELECT name FROM genre WHERE id=" . $data['genreId'];
                                            $resgenre = $conn->query($sqlgenre);
                                            while ($datagenre = mysqli_fetch_assoc($resgenre)) {
                                                echo $datagenre['name'];
                                            }
                                            ?></td>
                                        <td><?php echo ROUND($data['rating'], 2); ?></td>
                                        <td><?php echo $data['yearPub']; ?></td>
                                        <td><?php echo $data['Publisher']; ?></td>
                                        <td><?php echo $data['ISBN']; ?></td>
                                        <td><?php echo $data['img']; ?></td>
                                        <td></td>
                                        <td><a href="javascript:void(0)" class="full-delete" name="full-delete-book" id="<?php echo $data['id']; ?>"><i class="far fa-trash-alt"></i></a></td>
                                        <td><a href="javascript:void(0)" class="restore" name="restore-book" id="<?php echo $data['id']; ?>"><i class="far fa-trash-alt"></i></a></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">Нет данных о существующих книгах.</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>
            </div>
        </div>
    <?php } ?>
    </div>
    <script src="https://kit.fontawesome.com/a9f6196afa.js" crossorigin="anonymous"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
</body>

</html>
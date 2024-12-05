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
                },
                complete: function() { // Вызывается после успешного выполнения запроса
                location.reload();
                } // Перезагружаем страницу
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
                },
                complete: function() { // Вызывается после успешного выполнения запроса
                location.reload();
                } // Перезагружаем страницу
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
                },
                complete: function() { // Вызывается после успешного выполнения запроса
                location.reload();
                } // Перезагружаем страницу
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
            <div class="ves-action">
                <?php
                if ($_GET['cat'] == 'add-text') {
                    if (!empty($_GET['edit'])) {
                        $editId = $_GET['edit'];
                        $query = "SELECT * FROM textbook WHERE id=$editId";
                        $res = $conn->query($query);
                        $editData = mysqli_fetch_assoc($res);
                        $text = $editData['text'];
                        $bookid = $editData['bookid'];
                        $idAttr = "updateAuthorForm";
                    } else {
                        $text = "";
                        $bookid = "";

                        $editId = "";
                        $idAttr = "authorForm";
                    }
                ?>

                    <?php
                    if (isset($_POST['save']) && !empty($_GET['edit'])) {
                        $sqlbook = "SELECT id FROM book WHERE id='" . $_POST['bookid'] . "'";
                        $resbook = $conn->query($sqlbook);
                        $databook = mysqli_fetch_assoc($resbook);

                        $sql = "UPDATE textbook SET text=?, bookid=? WHERE id=?";
                        $stmt = mysqli_prepare($conn, $sql);
                        if ($stmt === false) {
                            die("Ошибка при подготовке заявки: " . mysqli_error($conn));
                        }

                        mysqli_stmt_bind_param($stmt, "sii", $_POST['text'], $databook['bookid'], $_GET['edit']);

                        if (mysqli_stmt_execute($stmt) === true) {
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Edit text', 'Edit text in book " . $databook['bookid'] . "')";
                            $stmt = mysqli_prepare($conn, $sqlTransaction);
                            if ($stmt === false) {
                                die("Ошибка при подготовке заявки: " . mysqli_error($conn));
                            }
                            $stmt->execute();
                        } else {
                            echo "Ошибка при обновлении записи: " . mysqli_error($conn);
                        }
                    } elseif (isset($_POST['save']) && empty($_GET['edit'])) {

                        $sqlbook = "SELECT id FROM book WHERE id='" . $_POST['bookid'] . "'";
                        $resbook = $conn->query($sqlbook);
                        $databook = mysqli_fetch_assoc($resbook);

                        if ($databook === null) {
                            echo "Ошибка: Книга с ID " . $_POST['bookid'] . " не найдена.";
                            return;
                        }

                        $text = trim($_POST['text']);

                        if (empty($text)) {
                            echo "Ошибка: Поле 'Текст' не может быть пустым.";
                            return;
                        }

                        $sql = "INSERT INTO textbook (text, bookid) VALUES (?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        if ($stmt === false) {
                            die("Ошибка при подготовке заявки: " . mysqli_error($conn));
                        }

                        mysqli_stmt_bind_param($stmt, "si", $text, $databook['id']);

                        if (mysqli_stmt_execute($stmt) === true) {
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Add text_book', 'Add text to book " . $databook['id'] . "')";
                            $stmt = mysqli_prepare($conn, $sqlTransaction);
                            if ($stmt === false) {
                                die("Ошибка при подготовке заявки: " . mysqli_error($conn));
                            }
                            $stmt->execute();
                            echo "Запись успешно добавлена и добавлена в журнал действий.";
                        } else {
                            echo "Ошибка при добавлении записи: " . mysqli_error($conn);
                        }
                    }
                    ?>


                    <div class="hheader">
                        <?php
                        if (empty($_GET['edit'])) {
                            echo "<h4 style='margin-left: 460px;'>Добавление текста</h4>";
                        } else {
                            echo "<h4 style='margin-left: 440px;'>Редактирование текста</h4>";
                        }
                        ?>
                        <a href="text_book.php"><button class="right">Перейти к обзору текстов</button></a>
                    </div>
                    <div class="view">
                        <form id="<?php echo $idAttr; ?>" rel="<?php echo $editId; ?>" name="author_profile" method="POST">
                            <div class="row">
                                <div class="containr">
                                    <input type="text" placeholder="id книги" name="bookid" value="<?php echo $bookid ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="containr_2">
                                    <input type="text" placeholder="Текст" name="text" value="<?php echo $text ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="submit-button" name="save">Сохранить</button>
                        </form>
                    </div>
                <?php  } else { ?>
                    <div class="hheader">
                        <h4>Управление текстами</h4>
                        <a href="text_book.php?cat=add-text"><button class="right">Добавить текст</button></a>
                    </div>
                    <div class="view">
                        <table>
                            <tr>
                                <th>Id книги</th>
                                <th></th>
                                <th>Редактировать</th>
                                <th>Удалить</th>
                            </tr>
                            <?php
                            $sql = "SELECT * FROM textbook WHERE flag = 1 ORDER BY id";
                            $res = $conn->query($sql);
                            if ($res->num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                            ?>
                                    <tr>
                                        <td><?php echo $data['bookid']; ?></td>
                                        <td></td>
                                        <td><a href="text_book.php?cat=add-text&edit=<?php echo $data['id']; ?>"><i class="far fa-edit"></i></a></td>
                                        <td><a class="delete" name="delete-text" id="<?php echo $data['id']; ?>" href="javascript:void(0)"><i class="far fa-trash-alt"></i></a></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">Нет данных о текстах.</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>
                    <br>
                    <div class="hheader">
                        <h4>Удаленные тексты</h4>
                    </div>
                    <div class="view">
                        <table>
                            <tr>
                                <th>Id книги</th>
                                <th></th>
                                <th>Удалить</th>
                                <th>Восстановить</th>
                            </tr>
                            <?php
                            $sql = "SELECT * FROM textbook WHERE flag = 0 ORDER BY id";
                            $res = $conn->query($sql);
                            if ($res->num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                            ?>
                                    <tr>
                                    <td><?php echo $data['bookid']; ?></td>
                                        <td></td>
                                        <td><a href="javascript:void(0)" class="full-delete" name="full-delete-text" id="<?php echo $data['id']; ?>"><i class="far fa-trash-alt"></i></a></td>
                                        <td><a href="javascript:void(0)" class="restore" name="restore-text" id="<?php echo $data['id']; ?>"><i class="far fa-edit"></i></a></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">Нет данных об удаленных текстах.</td>
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
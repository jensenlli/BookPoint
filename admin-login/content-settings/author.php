<?php
session_start();
$email_address = $_SESSION['email'];
include('../../config/dbConnect.php');
if (empty($email_address))
{
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
            var id=$(this).attr('id');
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
                    if ($_GET['cat'] =='add-author') {
                        if (!empty($_GET['edit'])) {
                            $editId = $_GET['edit'];
                            $query = "SELECT * FROM author WHERE id=$editId";
                            $res = $conn -> query($query);
                            $editData = mysqli_fetch_assoc($res);
                            $name = $editData['name'];
                            $idAttr = "updateAuthorForm";
                        } else {
                            $name = "";

                            $editId = "";
                            $idAttr = "authorForm";

                        }
                ?>

                <?php
                    if (isset($_POST['save'])) {
                        if (empty($_GET['edit'])) {
                            $sql = "INSERT INTO author (name) VALUES (?)";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "s", $_POST['name']);
                            mysqli_stmt_execute($stmt);
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Add author', 'Add author ".$_POST['name']."')";
                            $stmt = mysqli_prepare($conn, $sqlTransaction);
                            $stmt -> execute();
                        } else {
                            $sql = "UPDATE author SET name=? WHERE id=".$_GET['edit'];
                            $stmt = mysqli_prepare($conn, $sql);
                            $stmt -> bind_param("s", $_POST['name']);
                            $stmt -> execute();
                            $sqlTransaction = "INSERT INTO transactions (name, description) VALUES ('Edit Author', 'Edit author ".$_POST['name']."')";
                        }
                    }
                ?>
                <div class="hheader">
                    <?php
                        if (empty($_GET['edit'])) {
                            echo "<h4 style='margin-left: 460px;'>Добавление автора</h4>";
                        } else {
                            echo "<h4 style='margin-left: 440px;'>Редактирование автора</h4>";
                        }
                    ?>
                    <a href="author.php"><button class="right">Перейти к обзору авторов</button></a>
                </div>
                <div class="view">
                    <form id="<?php echo $idAttr; ?>" rel="<?php echo $editId; ?>" name="author_profile" method="POST">
                        <div class="name">
                            <span>Фамилия и имя</span>
                            <input type="text" placeholder="Фамилия и имя" name="name" value="<?php echo $name ?>" required>
                        </div>
                        <button type="submit" class="submit-button" name="save">Сохранить</button>
                    </form>
                </div>
                <?php  } else {?>
                <div class="hheader">
                    <h4>Управление авторами</h4>
                    <a href="author.php?cat=add-author"><button class="right">Добавить автора</button></a>
                </div>
                <div class="view">
                    <table>
                        <tr>
                            <th>Фамилия и имя</th>
                            <th></th>
                            <th>Редактировать</th>
                            <th>Удалить</th>
                        </tr>
                        <?php
                            $sql = "SELECT * FROM author WHERE flag = 1 ORDER BY id";
                            $res = $conn -> query($sql);
                            if ($res -> num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                        ?>
                        <tr>
                            <td><?php echo $data['name']; ?></td>
                            <td></td>
                            <td><a href="author.php?cat=add-author&edit=<?php echo $data['id']; ?>"><i class="far fa-edit"></i></a></td>
                            <td><a class="delete" name="delete-author" id="<?php echo $data['id']; ?>" href="javascript:void(0)"><i class="far fa-trash-alt"></i></a></td>
                        </tr>
                        <?php
                                }
                            } else {
                        ?>
                        <tr>
                            <td colspan="6">Нет данных о существующих авторах.</td>
                        </tr>
                        <?php
                            }
                        ?>
                    </table>
                </div>
                <br>
                <div class="hheader">
                    <h4>Удаленные авторы</h4>
                </div>
                <div class="view">
                    <table>
                        <tr>
                            <th>Фамилия и имя</th>
                            <th></th>
                            <th>Удалить</th>
                            <th>Восстановить</th>
                        </tr>
                        <?php
                            $sql = "SELECT * FROM author WHERE flag = 0 ORDER BY id";
                            $res = $conn -> query($sql);
                            if ($res -> num_rows > 0) {
                                while ($data = mysqli_fetch_assoc($res)) {
                        ?>
                        <tr>
                            <td><?php echo $data['name']; ?></td>
                            <td></td>
                            <td><a href="javascript:void(0)" class="full-delete" name="full-delete-author" id="<?php echo $data['id']; ?>"><i class="far fa-trash-alt"></i></a></td>
                            <td><a href="javascript:void(0)" class="restore" name="restore-author" id="<?php echo $data['id']; ?>"><i class="far fa-edit"></i></a></td>
                        </tr>
                        <?php
                                }
                            } else {
                        ?>
                        <tr>
                            <td colspan="6">Нет данных об удаленных авторах.</td>
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
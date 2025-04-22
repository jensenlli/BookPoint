<?php
session_start();
include('config/dbConnect.php');

if (!$_SESSION['user']) {
    header('Location: index.php');
}

$bookId = intval($_GET['book_id']);
$userId = $_SESSION['user']['id'] ?? null;

// Получаем информацию о книге
$bookSql = "SELECT name FROM book WHERE id = ?";
$bookStmt = $conn->prepare($bookSql);
$bookStmt->bind_param("i", $bookId);
$bookStmt->execute();
$bookResult = $bookStmt->get_result();
$book = $bookResult->fetch_assoc();

if (!$book) {
    die("Книга не найдена");
}

// Обработка отправки отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    if (isset($_POST['delete_review'])) {
        // Удаление отзыва
        $reviewId = intval($_POST['review_id']);
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $reviewId, $userId);
        $stmt->execute();
    } else {
        // Добавление/редактирование отзыва
        $reviewText = trim($_POST['review_text']);
        $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        $reviewId = $_POST['review_id'] ?? null;

        if (!empty($reviewText)) {
            try {
                if ($reviewId) {
                    // Редактирование
                    $stmt = $conn->prepare("UPDATE reviews 
                        SET review_text = ?, is_anonymous = ?, updated_at = NOW() 
                        WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("siii", $reviewText, $isAnonymous, $reviewId, $userId);
                } else {
                    // Добавление нового
                    $stmt = $conn->prepare("INSERT INTO reviews 
                        (user_id, book_id, review_text, is_anonymous) 
                        VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iisi", $userId, $bookId, $reviewText, $isAnonymous);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
                }
            } catch (Exception $e) {
                die("Произошла ошибка: " . $e->getMessage());
            }
        }
    }
}

// Получаем отзывы
$reviewsSql = "SELECT r.*, u.full_name 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE book_id = ?
                ORDER BY created_at DESC";
$stmt = $conn->prepare($reviewsSql);
$stmt->bind_param("i", $bookId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
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
    
    <style>
        .reviews-section {
            margin: 70px;
            max-width: max-content;
        }

        .review-block {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-author {
            color: #CC9600;
            font-weight: 500;
        }

        .review-date {
            color: #666;
            font-size: 0.9em;
        }

        .review-actions {
            display: flex;
            gap: 10px;
        }

        .review-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .anonymous-check {
            margin: 10px 0;
        }
    </style>
    <title>Отзывы на книгу <?= htmlspecialchars($book['name']) ?></title>
</head>

<body>
<div class="wrapper">
    <?php include('header.php'); ?>
    <div class="page">
        <img src="media/image5.jpg" class="imgreg" style="width: 600px; height:700px;">
        
        <!-- Секция с отзывами -->
        <div class="reviews-section">
            <h2>Отзывы на книгу "<?= htmlspecialchars($book['name']) ?>"</h2>

            <!-- Форма добавления отзыва -->
            <?php if($userId): ?>
            <form method="POST" class="review-form">
                <textarea name="review_text" 
                    placeholder="  Ваш отзыв..." 
                    rows="4"
                    required
                    class="form-control" style="width: 600px;background-color:  #88888817;"></textarea>
                <div class="anonymous-check">
                    <label style="font-size: small; ">
                        <input type="checkbox" name="is_anonymous">
                        Опубликовать анонимно
                    </label>
                </div>
                <button type="submit" class="inputpagesbtn">Отправить</button>
            </form>
            <?php endif; ?>

            <!-- Список отзывов -->
            <div class="review-list">
                <?php foreach($reviews as $review): ?>
                    <div class="review-block">
                        <div class="review-header">
                            <div>
                                <span class="review-author">
                                    <?= $review['is_anonymous'] 
                                        ? 'Анонимный пользователь' 
                                        : htmlspecialchars($review['full_name']) ?>
                                </span>
                                <span class="review-date">
                                    <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                                    <?= ($review['created_at'] != $review['updated_at'])
                                        ? '(изменено)' 
                                        : '' ?>
                                </span>
                            </div>
                            <?php if($review['user_id'] == $userId): ?>
                            <div class="review-actions">
                                <form method="POST">
                                    <input type="hidden" name="review_id" 
                                        value="<?= $review['id'] ?>">
                                    <button type="submit" name="delete_review" 
                                        class="delete-quote">×</button>
                                </form>
                                <button onclick="showEditForm(<?= $review['id'] ?>)" 
                                    class="btn-edit">✎</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>

                    <!-- Форма редактирования (скрытая) -->
                    <?php if($review['user_id'] == $userId): ?>
                    <form method="POST" 
                        class="review-form edit-form" 
                        id="editForm<?= $review['id'] ?>" 
                        style="display: none;">
                        <input type="hidden" name="review_id" 
                            value="<?= $review['id'] ?>">
                        <textarea name="review_text" 
                            rows="4"
                            required
                            class="form-control"><?= htmlspecialchars($review['review_text']) ?></textarea>
                        <div class="anonymous-check">
                            <label>
                                <input type="checkbox" name="is_anonymous"
                                    <?= $review['is_anonymous'] ? 'checked' : '' ?>>
                                Опубликовать анонимно
                            </label>
                        </div>
                        <button type="submit" class="inputpagesbtn">Сохранить</button>
                    </form>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php include('partials/footer.php'); ?>
</div>

<script>
function showEditForm(reviewId) {
    const form = document.getElementById(`editForm${reviewId}`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Подтверждение удаления
document.querySelectorAll('[name="delete_review"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (!confirm('Вы уверены, что хотите удалить отзыв?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
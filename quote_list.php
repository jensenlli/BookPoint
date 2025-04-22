<?php
session_start();
include('config/dbConnect.php');

if (!$_SESSION['user']) {
    header('Location: index.php');
}

$userId = $_SESSION['user']['id'];

    $sql = "SELECT bk.id AS quote_id, b.name AS book_name, a.name AS author_name, 
                   bk.quote_text, bk.created_at, bk.book_id, bk.page 
            FROM bookmarks bk
            JOIN book b ON bk.book_id = b.id
            LEFT JOIN author a ON b.authorId = a.id
            WHERE bk.user_id = ? AND bk.quote_text IS NOT NULL
            ORDER BY bk.created_at DESC";

// 1. Подготовка запроса
if (!$stmt = $conn->prepare($sql)) {
    throw new Exception("Prepare failed: " . $conn->error);
}

// 2. Привязка параметров
if (!$stmt->bind_param("i", $userId)) {
    throw new Exception("Bind param failed: " . $stmt->error);
}

// 3. Выполнение запроса
if (!$stmt->execute()) {
    throw new Exception("Execute failed: " . $stmt->error);
}

// 4. Получение результата
$result = $stmt->get_result();

$quotes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $quotes[] = $row;
    }
}

$stmt->close();
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
    
    <title>BookPoint</title>
    <style>
        .quote-block {
            position: relative;
            margin: 30px 0;
            padding: 25px 40px;
            background: #fff9e6;
            border-radius: 8px;
            font-style: italic;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .quote-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .delete-quote {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            color: #999;
            font-size: 16px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .delete-quote:hover {
            color: #ff4444;
            background: rgba(255,68,68,0.1);
        }

        .quote-meta {
            margin-top: 15px;
            font-style: normal;
            color: #666;
            font-size: 0.9em;
        }

        .book-author {
            color: #CC9600;
            font-weight: 500;
            margin-top: 5px;
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include('header.php'); ?>
        <div class="page">
            <img src="media/image4.jpg" class="imgreg">
            
            <div class="quotes-container" style="width: max-content;margin-right: 50px;">
                <?php if(!empty($quotes)): ?>
                    <?php foreach($quotes as $quote): ?>
                        <div class="quote-block" 
                             onclick="window.location.href='thisbook.php?id=<?= $quote['book_id'] ?>&page=<?= $quote['page'] ?>'">
                            
                            <div class="delete-quote" onclick="event.stopPropagation(); deleteQuote(<?= $quote['quote_id'] ?>, this)">
                                ×
                            </div>

                            <div class="quote-text"><?= htmlspecialchars($quote['quote_text']) ?></div>
                            
                            <div class="quote-meta">
                                <div class="book-name"><?= htmlspecialchars($quote['book_name']) ?></div>
                                <div class="book-author"><?= htmlspecialchars($quote['author_name'] ?? 'Неизвестный автор') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-quotes">У вас пока нет сохранённых цитат</div>
                <?php endif; ?>
            </div>
        </div>
        <?php include('partials/footer.php'); ?>
    </div>

    <script>
        function deleteQuote(quoteId, element) {
            if (!confirm('Удалить цитату?')) return;
            
            fetch('delete_quote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `quote_id=${quoteId}`
            })
            .then(response => {
                if (response.ok) {
                    element.closest('.quote-block').remove();
                    
                    // Если не осталось цитат, показываем сообщение
                    if (!document.querySelector('.quote-block')) {
                        document.querySelector('.quotes-container').innerHTML = `
                            <div class="no-quotes">У вас пока нет сохранённых цитат</div>
                        `;
                    }
                } else {
                    alert('Ошибка при удалении');
                }
            });
        }
    </script>
</body>
</html>
<?php
session_start();
include('config/dbConnect.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

// Получаем список книг с временем чтения
$booksSql = "SELECT b.id, b.name AS book_name, a.name AS author_name, 
            SUM(rs.total_seconds) AS total_seconds
            FROM reading_sessions rs
            JOIN book b ON rs.book_id = b.id
            JOIN author a ON b.authorId = a.id
            WHERE rs.user_id = ?
            GROUP BY b.id
            ORDER BY total_seconds DESC";

$stmt = $conn->prepare($booksSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Получаем данные для графика
$chartSql = "SELECT DATE(start_time) AS reading_date, 
            SUM(total_seconds) AS total_seconds
            FROM reading_sessions
            WHERE user_id = ?
            GROUP BY reading_date
            ORDER BY reading_date";

$stmt = $conn->prepare($chartSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$chartData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Формируем данные для графика
$labels = [];
$data = [];
foreach ($chartData as $row) {
    $labels[] = $row['reading_date'];
    $data[] = round($row['total_seconds'] / 3600, 2); // Переводим в часы
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моя статистика чтения</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats-section { margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .chart-container { max-width: 800px; margin: 40px auto; }
    </style>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="partials/css/footer.css">
    <link rel="stylesheet" href="partials/css/header.css">
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
    <p class="allbooks" style="font-size: x-large;">Моя статистика чтения</p>

        <!-- Список книг -->
        <div class="stats-section">
            <br>
            <h2>Прочитанные книги</h2>
            <table>
                <thead>
                    <tr>
                        <th>Книга</th>
                        <th>Автор</th>
                        <th>Время чтения</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['book_name']) ?></td>
                            <td><?= htmlspecialchars($book['author_name']) ?></td>
                            <td><?= gmdate("H:i:s", $book['total_seconds']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- График активности -->
        <div class="stats-section">
            <h2>Активность чтения</h2>
            <div class="chart-container">
                <canvas id="readingChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Инициализация графика
        const ctx = document.getElementById('readingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Часов чтения в день',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: 'rgba(204, 150, 0, 0.2)',
                    borderColor: 'rgba(204, 150, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Часы'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Дата'
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <?php include('partials/footer.php'); ?>
</body>
</html>
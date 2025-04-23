<?php
session_start();
include('config/dbConnect.php');
$userId = $_SESSION['user']['id'];

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// ТАЙМЕР
function getCurrentReadingTime($userId, $bookId) {
    include('config/dbConnect.php');
    $sql = "SELECT total_seconds 
            FROM reading_sessions 
            WHERE user_id = ? 
              AND book_id = ? 
              AND is_completed = FALSE 
            ORDER BY start_time DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total_seconds'] ?? 0;
}


// Проверяем есть ли закладка для этого пользователя
$bookmark_page = null;
if ($userId) {
    $sql = "SELECT page FROM bookmarks WHERE user_id = ? AND book_id = ? AND quote_text IS NULL  ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $bookmark_page = $result->fetch_assoc()['page'];
    }
}

// Определяем текущую страницу
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Если есть закладка И это первое открытие книги (нет явного page в URL)
if ($bookmark_page && !isset($_GET['page'])) {
    // Перенаправляем только если пользователь не указал конкретную страницу
    header("Location: thisbook.php?id=$id&page=$bookmark_page");
    exit;
}

// Функция для разбиения текста на страницы без разрыва слов
function paginateText($text, $chars_per_page, $page) {
    $words = explode(' ', $text);
    $current_length = 0;
    $pages = [];
    $current_page = [];
    
    foreach ($words as $word) {
        $word_length = strlen($word) + 1; // +1 для пробела
        
        if ($current_length + $word_length <= $chars_per_page || empty($current_page)) {
            $current_page[] = $word;
            $current_length += $word_length;
        } else {
            $pages[] = implode(' ', $current_page);
            $current_page = [$word];
            $current_length = $word_length;
        }
    }
    
    if (!empty($current_page)) {
        $pages[] = implode(' ', $current_page);
    }
    
    $total_pages = count($pages);
    $page = max(1, min($page, $total_pages));
    
    return [
        'text' => $pages[$page - 1] ?? '',
        'total_pages' => $total_pages,
        'current_page' => $page
    ];
}

// Получаем сохраненные цитаты для этой страницы
$quotes = [];
if ($userId) {
    $sql = "SELECT quote_text FROM bookmarks WHERE user_id = ? AND book_id = ? AND page = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $id, $page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quotes[] = $row['quote_text'];
    }
}

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

if (!$row) {
    echo "Книга не найдена.";
    exit;
}

// Получаем данные о количестве отзывов
$reviewCountSql = "SELECT COUNT(*) as count FROM reviews WHERE book_id = ?";
$reviewCountStmt = $conn->prepare($reviewCountSql);
$reviewCountStmt->bind_param("i", $id);
$reviewCountStmt->execute();
$reviewCount = $reviewCountStmt->get_result()->fetch_assoc()['count'];

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
$stmt->close();
$conn->close();

// Параметры пагинации
$chars_per_page = 15000; // Примерное количество символов на страницу
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Разбиваем текст на страницы
$pagination_result = paginateText($text, $chars_per_page, $page);
$page_text = $pagination_result['text'];
$total_pages = $pagination_result['total_pages'];
$page = $pagination_result['current_page'];
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
                <div class="reviews-section">
                    <a href="reviews.php?book_id=<?= $id ?>" class="inputpagesbtn"> Отзывы (<?= $reviewCount ?>)</a>
                </div>
                <br><br>
                
                 <!-- Таймер -->
                <div class="timer-container">
                    <div>Время чтения: <span id="readingTimer">00:00:00</span></div>
                    <?php if ($page == $total_pages): ?>
                        <button id="stopTimerButton" onclick="stopTimerPermanently()">Завершить чтение</button>
                    <?php endif; ?>
                </div>
                
                <!-- Ярлык закладки -->
                <div class="bookmark-ribbon <?php echo ($page == $bookmark_page) ? 'bookmark-active' : 'bookmark-inactive'; ?>" 
                    id="bookmarkRibbon"
                    title="<?php echo ($page == $bookmark_page) ? 'Закладка на этой странице' : 'Кликните чтобы поставить закладку'; ?>">
                    ★
                </div>

                <form method="GET" action="thisbook.php">
                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                    <input type="number" class="inputpages" name="page" min="1" max="<?php echo $total_pages; ?>" placeholder="Введите номер страницы" required />
                    <button type="submit" class="inputpagesbtn">Перейти</button>
                </form>

                <form method="POST" action="save_bookmark.php">
                    <input type="hidden" name="book_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                </form>
                <br><br>
                <?php
                // Проверяем, что у нас есть текст для отображения
                if (!empty(trim($page_text))) {
                    // Заменяем переносы строк на <br> после проверки на пустоту
                    echo nl2br(htmlspecialchars($page_text, ENT_QUOTES, 'UTF-8'));
                } else {
                    echo "Текст книги пока что не доступен. Попробуйте зайти немного позднее";
                }
                ?>
                <br><br><br><br>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a style="color:#CC9600; padding-bottom: 50px;" href="thisbook.php?id=<?php echo $id; ?>&page=<?php echo $page - 1; ?>">« Предыдущая</a>
                    <?php endif; ?>

                    <span style="padding: 0 15px;">Страница <?php echo $page; ?> из <?php echo $total_pages; ?></span>

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
        <span class="close" onclick="document.getElementById('ratingModal').style.display='none'">&times;</span>
            <h1 id="modalText" class="hidden">Вы очень близко к окончанию истории...<br>
                Осталась всего одна страница ♡</h1>
            <br></br>
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
            const modalText = document.getElementById('modalText');
            let selectedRating = null;

            // Открыть модальное окно на 15-й странице или на последней странице
            const currentPage = <?php echo $page; ?>;
            const totalPages = <?php echo $total_pages; ?>;

            if (currentPage === 15 || currentPage === totalPages) {
                ratingModal.style.display = "block";

                // Показать текст, если это последняя страница
                if (currentPage === totalPages) {
                    modalText.classList.remove('hidden'); // Убираем класс hidden
                    modalText.classList.add('visible'); // Добавляем класс visible
                } else {
                    modalText.classList.remove('visible'); // Убираем класс visible
                    modalText.classList.add('hidden'); // Добавляем класс hidden
                }
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookmarkRibbon = document.getElementById('bookmarkRibbon');
        
        // Обработчик клика по ярлыку
        bookmarkRibbon.addEventListener('click', function() {
            const isActive = this.classList.contains('bookmark-active');
            
            fetch('save_bookmark.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=<?php echo $id; ?>&page=<?php echo $page; ?>`
            })
            .then(response => {
                if (response.ok) {
                    // Просто обновляем стиль, без перезагрузки
                    this.classList.toggle('bookmark-inactive');
                    this.classList.toggle('bookmark-active');
                    this.title = this.classList.contains('bookmark-active') 
                        ? 'Закладка на этой странице' 
                        : 'Кликните чтобы поставить закладку';
                    
                    // Анимация
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => this.style.transform = 'scale(1)', 300);
                }
            });
        });

        // Автопрокрутка только если это страница с закладкой И нет явного page в URL
        <?php if ($page == $bookmark_page && !isset($_GET['page'])): ?>
            setTimeout(() => {
                window.scrollTo({
                    top: document.querySelector('.card-body').offsetTop - 100,
                    behavior: 'smooth'
                });
            }, 300);
        <?php endif; ?>
    });
</script>

<!-- ДОБАВЛЯЕМ МОДАЛЬНОЕ ОКНО ДЛЯ ЦИТАТ -->
<div id="quoteModal">
        <button onclick="saveHighlight()">Выделить цитату</button>
    </div>

    <script>
        // ДОБАВЛЯЕМ ОБРАБОТЧИК ВЫДЕЛЕНИЯ ТЕКСТА
        let currentSelection = null;

        document.addEventListener('mouseup', function(e) {
            const selection = window.getSelection();
            if (!selection.isCollapsed && e.button === 0) {
                const range = selection.getRangeAt(0);
                const rect = range.getBoundingClientRect();
                
                // Показываем кнопку рядом с выделением
                const modal = document.getElementById('quoteModal');
                modal.style.display = 'block';
                modal.style.top = `${rect.top + window.scrollY - 40}px`;
                modal.style.left = `${rect.left + window.scrollX}px`;
                
                currentSelection = range;
            }
        });

        // Функция сохранения выделения
        function saveHighlight() {
            const modal = document.getElementById('quoteModal');
            if (!currentSelection) return;

            const selectedText = currentSelection.toString().trim();
            if (selectedText.length === 0) return;

            // Добавляем визуальное выделение
            const span = document.createElement('span');
            span.className = 'highlight';
            span.textContent = selectedText;
            currentSelection.deleteContents();
            currentSelection.insertNode(span);

            // Сохраняем в базу данных
            fetch('save_quote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=<?php echo $id; ?>&page=<?php echo $page; ?>&quote_text=${encodeURIComponent(selectedText)}`
            })
            .then(response => {
                if (response.ok) {
                    modal.style.display = 'none';
                    span.style.background = 'rgba(255,215,0,0.3)'; // Фиксируем цвет
                }
            });

            currentSelection = null;
        }

        // Восстанавливаем сохраненные выделения при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($quotes as $quote): ?>
                const textContent = document.body.textContent;
                const startIndex = textContent.indexOf('<?php echo addslashes($quote); ?>');
                
                if (startIndex !== -1) {
                    const range = document.createRange();
                    const textNode = document.body.childNodes[0];
                    range.setStart(textNode, startIndex);
                    range.setEnd(textNode, startIndex + <?php echo mb_strlen($quote); ?>);
                    
                    const span = document.createElement('span');
                    span.className = 'highlight';
                    range.surroundContents(span);
                }
            <?php endforeach; ?>
        });
    </script>

<script>
class ActiveReadingTimer {
    constructor(bookId) {
        this.bookId = bookId;
        this.startTime = 0;
        this.totalSeconds = 0;
        this.timerInterval = null;
        this.isActive = false;
    }

    async start() {
        if (this.isActive) return;
        
        try {
            // Уведомляем сервер о начале чтения
            await fetch('start_reading.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=${this.bookId}`
            });
            
            // Загружаем общее время
            const response = await fetch(`get_reading_time.php?book_id=${this.bookId}`);
            const data = await response.json();
            this.totalSeconds = data.total_seconds;
            
            // Запускаем таймер
            this.startTime = Date.now();
            this.isActive = true;
            this.timerInterval = setInterval(() => this.update(), 1000);
            
        } catch (error) {
            console.error('Ошибка старта таймера:', error);
        }
    }

    async stop() {
        if (!this.isActive) return;
        
        clearInterval(this.timerInterval);
        this.isActive = false;
        
        try {
            // Уведомляем сервер об остановке
            await fetch('stop_reading.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=${this.bookId}`
            });
        } catch (error) {
            console.error('Ошибка остановки таймера:', error);
        }
    }

    update() {
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const displaySeconds = this.totalSeconds + elapsed;
        this.updateDisplay(displaySeconds);
    }

    updateDisplay(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        
        document.getElementById('readingTimer').textContent = 
            `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.readingTimer = new ActiveReadingTimer(<?= $id ?>);
    readingTimer.start();
});

// Остановка при закрытии страницы
window.addEventListener('beforeunload', () => {
    if (window.readingTimer) {
        readingTimer.stop();
        
        // Синхронный запрос для гарантированной остановки
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'stop_reading.php', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(`book_id=${<?= $id ?>}`);
    }
});

// Для кнопки завершения чтения (если есть)
const stopBtn = document.getElementById('stopTimerButton');
if (stopBtn) {
    stopBtn.addEventListener('click', async () => {
        await readingTimer.stop();
        
        // Дополнительные действия при завершении чтения
        try {
            await fetch('complete_reading.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=${<?= $id ?>}`
            });
        } catch (error) {
            console.error('Ошибка завершения чтения:', error);
        }
    });
}
</script>

</body>
</html>
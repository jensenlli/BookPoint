<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/header.css">
    <title>DigitalBook</title>
</head>
<body>
    <div class="wrapper">
    <?php include('../header.php'); ?>
        <p><h1>УВЕДОМЛЕНИЕ О ПРИМЕНЕНИИ РЕКОМЕНДАТЕЛЬНЫХ ТЕХНОЛОГИЙ</h1></p>
        <p>&nbsp</p>
        <p>На информационном ресурсе при применении информационных технологий предоставления информации осуществляется сбор, систематизация и анализ сведений, относящихся к предпочтениям пользователей сети "Интернет", находящихся на территории Российской Федерации".</p>
        <p>&nbsp</p>

        <p>Name – сервис для чтения электронных текстовых книг и прослушивания аудиокниг, размещенный на Интернет сайте https://www. (далее «Сервис»).</p>
        <p>&nbsp</p>
        <p>Настоящим создатель Сервиса ООО «Name» информирует о применении рекомендательных технологий на Сервисе.</p>

        <p>Рекомендательные технологии - информационные технологии предоставления информации на основе сбора, систематизации и анализа сведений, относящихся к предпочтениям пользователей сети Интернет.</p>
        <p>&nbsp</p>
        <p>&nbsp</p>
        <p><h2>ПРАВИЛА ПРИМЕНЕНИЯ РЕКОМЕНДАТЕЛЬНЫХ ТЕХНОЛОГИЙ</h2></p>
        <p>&nbsp</p>

<p>Настоящие Правила регулируют применение рекомендательных технологий на Сервисе.
Рекомендательные технологии на Сервисе не нарушают права и законные интересы граждан и организаций, а также не применяются в целях предоставления информации с нарушением законодательства Российской Федерации.</p>
<p>&nbsp</p>
<p>Сервис использует рекомендательные технологии, чтобы предсказать заинтересованность пользователя в услуге (книгb) на основе предпочтений других пользователей, похожих на данного. Рекомендации используются в виде списков единиц контента: полки с книгами и подкастами, списки жанров, тэгов, авторов.</p>
<p>&nbsp</p>
<p>Описание процессов и методов сбора, систематизации, анализа сведений, относящихся к предпочтениям пользователей сети «Интернет», предоставления информации на основе этих сведений, способов осуществления таких процессов и методов:
Данные, указанные в п.5. Правил, Сервис использует для:</p>

<p>&nbsp</p>
• Отображения рекомендаций списка книг на витринах Сервиса на основании данных о взаимодействии с другими объектами Сервиса (книга, жанр);
<p>&nbsp</p>
• Отображения книг, похожих на друг на друга;
<p>&nbsp</p>
с После препроцессинга исходных данных создается разряженная матрица с ID пользователей в строках и ID элементов в столбцах. Весь алгоритм строится на выполнении одного из методов (моделей) матричной факторизации
<p>&nbsp</p>

Пару слов о модели KNN
<p>&nbsp</p>
При невозможности определения рекомендаций пользователю с помощью матрицы (например, у пользователя не было действий с объектами), в качестве рекомендаций используются самые покупаемые объекты на языке пользователя за определенный период (период зависит от языка, например, для русского языка период составляет один день).
<p>&nbsp</p>

Описание видов сведений, относящихся к предпочтениям пользователей сети Интернет, которые используются для предоставления информации с применением рекомендательных технологий, и источников получения таких сведений:
Рекомендательные технологии, используемые в Сервисе, обрабатывают данные на основе совершенных пользователем действий.  Примерами таких действий могут быть: посещение страницы/экрана, клик по элементу, прокрутка, покупка книги, чтение книги, оценка книги и другие.  Хранение данных осуществляется на серверах Сервиса.
<p>&nbsp</p>

Для обучения моделей используются следующие данные:
<p>&nbsp</p>
• просмотр страницы книги более 8 секунд;
<p>&nbsp</p>
• добавление книги в отложенные;
<p>&nbsp</p>
• оценка книги;
<p>&nbsp</p>
• чтение книги.</p>
</div>
<?php include('footer.php'); ?>
</body> 
</html>
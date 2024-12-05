
<script>
    document.body.addEventListener("click", function() {
        var evt = window.event || evt;
        var obj = evt.target.id;
        if (obj == "dropdownbtn") {
            document.getElementById("content").classList.toggle("show");
        } else {
            if (document.getElementById("content").classList.toggle("show") == true)
                document.getElementById("content").classList.toggle("show");
        }
        if (obj == "dropdownbtn2") {
            document.getElementById("content2").classList.toggle("show2");
        } else {
            if (document.getElementById("content2").classList.toggle("show2") == true)
                document.getElementById("content2").classList.toggle("show2");
        }

    }, true);

    document.body.addEventListener("click", function() {
        var evt = window.event || evt;
        var obj = evt.target.id;
        if (obj == "dropdownbtn2") {
            document.getElementById("content2").classList.toggle("show2");
        } else {
            if (document.getElementById("content2").classList.toggle("show2") == true)
                document.getElementById("content2").classList.toggle("show2");
        }
    })
</script>
<style>
    .sidebar {
        width: 300px;
        height: 100%;
        background-color: #D6CE80;
        color: black;
        font-size: 18px;
        opacity: 0.7;
    }
    .dropdownbtn {
        display: block;
        font-weight: 500;
        border: none;
        outline: none;
        color: black;
        padding: 10px 30px;
        background-color: #D6CE80;
        font-family: inherit; /* Важно для вертикального выравнивания на мобильных телефонах */
        margin: 0; /* Важно для вертикального выравнивания на мобильных телефонах */
        width: 300px;
        text-align: center;
        height: auto;
        font-size: 18px;
        border-bottom: 2px solid #CC9600;
    }

    .dropdownbtn span {
        font-size: 18px;
    }
    .drpdwn {
        display: flex;
        align-items: center;
    }
    .drpdwn i{
        margin-right: 5px;
        font-size: 10px;
    }
    .sb-element a{
        color: black;
        font-weight: 500;
        font-size: 18px;
    }
    .content {
        max-height:0px;
        overflow:hidden;
        transition-duration: 1.5s;
    }
    .show {
        max-height: 300px;
    }

    .content-link {
        display: block;
        text-align: right;
        color: black;
        padding: 20px 30px;
    }
    .content-link:hover {
        background-color: #2d2d2d; 
    }
</style>
<div class="sidebar">
    <div class="sb-element">
        <div class="drpdwn">
            <button class="dropdownbtn" id="dropdownbtn">
                <span id="dropdownbtn">Управление контентом</span>
                <i class="fas fa-chevron-down" id="dropdownbtn"></i>
            </button>
        </div>
        <div class="content" id="content">
            <ul class="content-list">
                <li class="content-element"><a href="/admin-login/content-settings/author.php" class="content-link">Авторы</a></li>
                <li class="content-element"><a href="/admin-login/content-settings/genre.php" class="content-link">Жанры</a></li>
                <li class="content-element"><a href="/admin-login/content-settings/book.php" class="content-link">Книги</a></li>
                <li class="content-element"><a href="/admin-login/content-settings/text_book.php" class="content-link">Тексты</a></li>
            </ul>
        </div>
    </div>
    <div class="sb-element">
        <a href="/admin-login/content-settings/admin-managing.php" class="dropdownbtn">Управление администраторами</a>
    </div>
    <div class="sb-element">
        <a href="/admin-login/content-settings/transactions.php" class="dropdownbtn">Управление транзакциями</a>
    </div>
</div>

<style>
    .container {
        display: flex;
        background-image: url("../../media/input/header.png");
        width: 100%;
        height: 80px;
    }

    .banner {
        padding: 20px 30px;
        color: white;
    }
    
    .banner h4 {
        color: white;
    }

    .rightbar {
        display: flex;
        margin-left: auto;
        margin-right: 0em;
    }

    .navbar {
        display: flex;
        overflow: hidden;
        align-items:right;
    }
    /* Ссылки в панели навигации */
    .navbar a {
        float: left;
        font-size: 16px;
        background-color: #D6CE90;
        color: white;
        text-align: center;
        padding: 20px 30px;
        text-decoration: none;
        width: 135px;
    }


    /* Выпадающий контейнер */
    .dropdown {
        float: left;
        overflow: hidden;
    }

    /* Кнопка выпадающего списка */
    .dropdown .dropbtn {
        font-size: 16px;
        border: none;
        outline: none;
        color: white;
        padding: 20px 30px;
        background-color: inherit;
        font-family: inherit; /* Важно для вертикального выравнивания на мобильных телефонах */
        margin: 0; /* Важно для вертикального выравнивания на мобильных телефонах */
        width: 135px;
    }


    .dropdown:hover .dropdown-content {
        max-height: 300px;
    }

    /* Выпадающее содержимое (скрыто по умолчанию) */
    .dropdown-content {
        max-height:0px;
        overflow:hidden;
        -webkit-transition:max-height 0.4s linear;
        -moz-transition:max-height 0.4s linear;
        transition:max-height 0.4s linear;
        position: absolute;
        background-color: #D6CE80;
        color: white;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }

    /* Ссылки внутри выпадающего списка */
    .dropdown-content a {
        float: none;
        color: white;
        padding: 20px 30px;
        text-decoration: none;
        display: inline-block;
        text-align: left;
        width: 100%;
    }

    /* Добавить серый цвет фона для выпадающих ссылок при наведении курсора */
    .dropdown-content a:hover {
        background-color: #D6CE80;
    }

    /* Показать выпадающее меню при наведении курсора */
    .menubox {
        background-color: #D6CE80;
        padding: 0 30px;
        color: white;
        align-items: right;
    }

    a:visited {
        color: white;
    }
</style>

<div class="container ">
    <a class="banner" href="/admin-login/dashboard.php">
        <h4>Панель администрирования</h4>
    </a>
    <div class="rightbar">
        <div class="navbar">
            <div class="dropdown">
                <button class="button dropbtn" style="width: 210px;">Администратор</button>
                <div class="dropdown-content">
                    <a href="../index.php"><i class='fas fa-sign-out-alt'></i>Выйти</a>
                </div>
            </div>
        </div>
    </div>
</div>
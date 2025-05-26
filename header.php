<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Online movie booking system</title>
    <link rel="stylesheet" href="./Assets/css/index.css" onerror="this.style.display='none';console.log('File index.css not found');">
    <link rel="stylesheet" href="./Assets/css/base.css" onerror="this.style.display='none';console.log('File base.css not found');">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./Assets/css/login.css" onerror="this.style.display='none';console.log('File login.css not found');">
    <link rel="stylesheet" href="./Assets/css/resgistion.css" onerror="this.style.display='none';console.log('File resgistion.css not found');">
    
    <style>
        /* Modal tìm kiếm */
        .header-search-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
        }

        .header-search-modal-content {
            background-color: #ffffff;
            margin: 10% auto;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header-search-modal-content h2 {
            font-size: 1.8rem;
            color: #333333;
            margin-bottom: 20px;
        }

        .header-search-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .header-search-input {
            padding: 8px 12px;
            font-size: 1.6rem;
            border: 1px solid #333333;
            border-radius: 5px;
            width: 100%;
        }

        .header-filter-select {
            padding: 8px 12px;
            font-size: 1.6rem;
            border: 1px solid #333333;
            border-radius: 5px;
            background-color: #ffffff;
            color: #333333;
            width: 100%;
        }

        .header-search-submit {
            padding: 8px 12px;
            font-size: 1.6rem;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .header-search-submit:hover {
            background-color: #0056b3;
        }

        .header-close-modal {
            padding: 8px 12px;
            font-size: 1.6rem;
            border: none;
            border-radius: 5px;
            background-color: #ff6b6b;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .header-close-modal:hover {
            background-color: #e63946;
        }
    </style>
</head>

<body>
    <div id="content">
        <div id="header">
            <div class="grid">
                <div class="navbar">
                    <ul>
                        <li>
                            <a href="./index.php?act=home"> <img class="logo" src="./Assets/images/logo.jpg" alt="" onerror="this.style.display='none';console.log('File logo.jpg not found');"></a>
                        </li>
                        <li>
                            <a href="./index.php?act=home">Trang chủ</a>
                        </li>
                        <li>
                            <a href="./index.php?act=schedule">Lịch chiếu</a>
                        </li>
                        <li>
                            <a href="./index.php?act=ticket_price">Giá vé</a>
                        </li>
                        <!-- <li>
                            <a href="./index.php?act=promotions">Khuyến mãi</a>
                        </li> -->
                        <!-- <li>
                            <a href="./index.php?act=about">Giới thiệu</a>
                        </li> -->
                        <?php
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] === 'admin') {
                            echo '<li><a href="./admin_dashboard.php?act=admin_promotions">Khuyến mãi</a></li>';
                            echo '<li class="resgistion"><a href="./admin_dashboard.php?act=manage_movies">Quản lý phim</a></li>';
                            echo '<li class="resgistion"><a href="./admin_dashboard.php?act=manage_tickets">Quản lý vé</a></li>';
                            echo '<li class="resgistion"><a href="./index.php?act=logout">Đăng xuất</a></li>';
                        }
                        elseif (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] === 'user') {
                            echo '<li><a href="./index.php?act=promotions">Khuyến mãi</a></li>';
                            echo '<li class="resgistion"><a href="./index.php?act=logout">Đăng xuất</a></li>';
                        } else {
                            echo '<li><a href="./index.php?act=promotions">Khuyến mãi</a></li>';
                            echo '<li class="resgistion"><a href="./index.php?act=register">Đăng ký</a></li>';
                            echo '<li class="log_in"><a href="./index.php?act=login">Đăng nhập</a></li>';
                        }
                        ?>
                        <li>
                            <a>
                                <i class="fa-solid fa-magnifying-glass" onclick="openSearchModal()"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
         <!-- Modal tìm kiếm -->
         <div id="searchModal" class="header-search-modal">
            <div class="header-search-modal-content">
                <h2>Tìm kiếm phim</h2>
                <form class="header-search-form" action="?act=home" method="GET">
                    <input type="hidden" name="act" value="home">
                    <input type="text" class="header-search-input" name="search" placeholder="Nhập tên phim..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <select class="header-filter-select" name="genre">
                        <option value="">Tất cả thể loại</option>
                        <option value="Hành động" <?php echo (isset($_GET['genre']) && $_GET['genre'] === 'Hành động') ? 'selected' : ''; ?>>Hành động</option>
                        <option value="Hài hước" <?php echo (isset($_GET['genre']) && $_GET['genre'] === 'Hài hước') ? 'selected' : ''; ?>>Hài hước</option>
                        <option value="Tình cảm" <?php echo (isset($_GET['genre']) && $_GET['genre'] === 'Tình cảm') ? 'selected' : ''; ?>>Tình cảm</option>
                        <option value="Kinh dị" <?php echo (isset($_GET['genre']) && $_GET['genre'] === 'Kinh dị') ? 'selected' : ''; ?>>Kinh dị</option>
                        <!-- Thêm các thể loại khác nếu cần -->
                    </select>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="header-search-submit">Tìm kiếm</button>
                        <button type="button" class="header-close-modal" onclick="closeSearchModal()">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openSearchModal() {
            document.getElementById('searchModal').style.display = 'block';
        }

        function closeSearchModal() {
            document.getElementById('searchModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('header-search-modal')) {
                closeSearchModal();
            }
        };
    </script>
</body>
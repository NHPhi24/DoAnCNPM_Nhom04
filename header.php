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
                        <li>
                            <a href="./index.php?act=promotions">Khuyến mãi</a>
                        </li>
                        <li>
                            <a href="./index.php?act=about">Giới thiệu</a>
                        </li>
                        <?php
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] === 'user') {
                            echo '<li class="resgistion"><a href="./index.php?act=logout">Đăng xuất</a></li>';
                        } else {
                            echo '<li class="resgistion"><a href="./index.php?act=register">Đăng ký</a></li>';
                            echo '<li class="log_in"><a href="./index.php?act=login">Đăng nhập</a></li>';
                        }
                        ?>
                        <li>
                            <a href="./index.php?act=search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
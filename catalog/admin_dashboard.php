<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            color: #007bff;
            text-decoration: none;
        }
        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chào mừng đến với Dashboard Quản trị viên</h1>
        <p>Đây là trang quản trị viên. Bạn có thể quản lý hệ thống từ đây.</p>
        <div class="logout">
            <a href="Log_in.html">Đăng xuất</a>
        </div>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: Log_in.php");
    exit();
}
?>
<h1>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
<p>Vai trò: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
<a href="logout.php">Đăng xuất</a>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Đăng nhập - Dark Theme</title>
    <link rel="stylesheet" href="./Assets/css/login.css" onerror="this.style.display='none';console.log('File login.css not found');">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bodyweb">
    <div class="login-container">
        <div class="login-header">
            <h1>Đăng nhập</h1>
            <p>Vui lòng nhập thông tin đăng nhập của bạn</p>
        </div>

        <form id="loginForm" class="login-form" method="post">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>
                <a href="#" class="forgot-password">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="login-button">Đăng nhập</button>

            <div class="signup-link">
                Chưa có tài khoản? <a href="./index.php?act=register">Đăng ký ngay</a>
            </div>

            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                require_once './backend/connect.php';

                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                $errors = [];

                if (empty($username)) {
                    $errors[] = "Tên đăng nhập là bắt buộc";
                }
                if (empty($password)) {
                    $errors[] = "Mật khẩu là bắt buộc";
                }

                if (empty($errors)) {
                    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();

                        if (password_verify($password, $user['password'])) {
                            $_SESSION['loggedin'] = true;
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['user_id'] = $user['id']; // Lưu user_id vào session
                            $_SESSION['role'] = $user['role'];

                            if ($user['role'] === 'admin') {
                                header("Location: ./catalog/admin_dashboard.php");
                            } else {
                                // Chuyển hướng đến index.php với id của user
                                header("Location: ./index.php?id=" . $user['id']);
                            }
                            exit();
                        } else {
                            $errors[] = "Mật khẩu không đúng";
                        }
                    } else {
                        $errors[] = "Tên đăng nhập không tồn tại";
                    }
                    $stmt->close();
                }

                if (!empty($errors)) {
                    echo '<div class="message error" style="margin-top: 10px; padding: 10px; color: red; text-align: center;">' . htmlspecialchars(implode(", ", $errors)) . '</div>';
                }

                $conn->close();
            }
            ?>
        </form>
    </div>

    <script src="./Assets/JS/login.js" onerror="this.onerror=null;console.log('File login.js not found');"></script>
</body>

</html>
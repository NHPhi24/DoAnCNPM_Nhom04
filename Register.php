<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Dark Theme</title>
    <link rel="stylesheet" href="./Assets/css/resgistion.css" onerror="this.style.display='none';console.log('File resgistion.css not found');">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: relative;
            color: #fff;
        }

        .modal-content.success {
            border: 2px solid #28a745;
        }

        .modal-content.error {
            border: 2px solid #dc3545;
        }

        .modal-content p {
            margin: 0 0 15px;
            font-size: 16px;
        }

        .modal-content .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-content .close-btn:hover {
            color: #fff;
        }

        .modal-content .action-btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }

        .modal-content .action-btn:hover {
            background-color: #0056b3;
        }

        .modal[style*="display: block"] {
            display: flex !important;
        }
    </style>
</head>

<body class="bodyweb">
    <div class="register-container">
        <div class="register-header">
            <h1>Tạo tài khoản mới</h1>
            <p>Điền thông tin để bắt đầu trải nghiệm</p>
        </div>

        <form id="registerForm" class="register-form" method="post">
            <div class="form-row">
                <div class="form-group half-width">
                    <label for="firstName">Họ</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="firstName" name="firstName" placeholder="Nhập họ của bạn" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-group half-width">
                    <label for="lastName">Tên</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Nhập tên của bạn" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Nhập email của bạn" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <div class="input-with-icon">
                    <i class="fas fa-at"></i>
                    <input type="text" id="username" name="username" placeholder="Chọn tên đăng nhập" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Tạo mật khẩu" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="strength-meter">
                        <span class="strength-bar weak"></span>
                        <span class="strength-bar medium"></span>
                        <span class="strength-bar strong"></span>
                    </div>
                    <small class="strength-text">Mật khẩu yếu</small>
                </div>
                <div class="password-hint">
                    <small>Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt</small>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Xác nhận mật khẩu</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Nhập lại mật khẩu" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('confirmPassword')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group terms-group">
                <label class="terms-checkbox">
                    <input type="checkbox" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                    Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a>
                </label>
            </div>

            <button type="submit" class="register-button">Đăng ký</button>

            <div class="login-promt">
                Bạn đã có tài khoản? <a href="./index.php?act=login">Đăng nhập tại đây</a>
            </div>
        </form>

        <?php
        $displayModal = false;
        $modalClass = '';
        $modalMessage = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            require_once './backend/connect.php';

            $firstName = trim($_POST['firstName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $termsChecked = isset($_POST['terms']);

            $errors = [];

            if (empty($firstName)) $errors[] = "Họ là bắt buộc";
            if (empty($lastName)) $errors[] = "Tên là bắt buộc";
            if (empty($email)) $errors[] = "Email là bắt buộc";
            if (empty($username)) $errors[] = "Tên đăng nhập là bắt buộc";
            if (empty($password)) $errors[] = "Mật khẩu là bắt buộc";

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email không hợp lệ";
            }

            if (strlen($password) < 8) {
                $errors[] = "Mật khẩu phải có ít nhất 8 ký tự";
            }

            if ($password !== $confirmPassword) {
                $errors[] = "Mật khẩu xác nhận không khớp";
            }

            if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
                $errors[] = "Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt";
            }

            if (!$termsChecked) {
                $errors[] = "Vui lòng đồng ý với điều khoản dịch vụ";
            }

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email đã được sử dụng";
            }
            $stmt->close();

            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Tên đăng nhập đã được sử dụng";
            }
            $stmt->close();

            if (empty($errors)) {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    if ($hashedPassword === false) {
                        throw new Exception("Lỗi khi mã hóa mật khẩu");
                    }

                    $role = 'user'; // Mặc định là user, admin sẽ được thêm thủ công
                    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $username, $hashedPassword, $role);
                    if ($stmt->execute()) {
                        $displayModal = true;
                        $modalClass = 'success';
                        $modalMessage = 'Đăng ký thành công! Vui lòng <a href="./index.php?act=login" style="color: #007bff;">đăng nhập</a> để tiếp tục.';
                        echo '<script>document.getElementById("registerForm").reset();</script>';
                    } else {
                        throw new Exception("Lỗi khi lưu dữ liệu: " . $conn->error);
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    $displayModal = true;
                    $modalClass = 'error';
                    $modalMessage = "Lỗi khi đăng ký: " . htmlspecialchars($e->getMessage());
                }
            } else {
                $displayModal = true;
                $modalClass = 'error';
                $modalMessage = htmlspecialchars(implode(", ", $errors));
            }

            $conn->close();
        }
        ?>

        <div id="modal" class="modal" style="display: <?php echo $displayModal ? 'block' : 'none'; ?>;">
            <div class="modal-content <?php echo $modalClass; ?>">
                <span class="close-btn" onclick="document.getElementById('modal').style.display='none'">×</span>
                <p><?php echo $modalMessage; ?></p>
                <button class="action-btn" onclick="document.getElementById('modal').style.display='none'">Đóng</button>
            </div>
        </div>
    </div>

    <script src="./Assets/JS/resgister.js" onerror="this.onerror=null;this.src='./Assets/JS/resgister.js';console.log('File register.js not found, trying resgister.js');"></script>
</body>

</html>
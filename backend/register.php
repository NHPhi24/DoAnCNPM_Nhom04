<?php
// Kết nối cơ sở dữ liệu
require_once 'connect.php';

// Bật báo lỗi chi tiết cho debug


// Xử lý form đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $termsChecked = isset($_POST['terms']);

    // Log dữ liệu nhận được
    error_log("Dữ liệu nhận được: " . print_r($_POST, true));

    // Validation
    $errors = [];

    // Kiểm tra các trường bắt buộc
    if (empty($firstName)) $errors[] = "Họ là bắt buộc";
    if (empty($lastName)) $errors[] = "Tên là bắt buộc";
    if (empty($email)) $errors[] = "Email là bắt buộc";
    if (empty($username)) $errors[] = "Tên đăng nhập là bắt buộc";
    if (empty($password)) $errors[] = "Mật khẩu là bắt buộc";

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    // Kiểm tra độ dài mật khẩu
    if (strlen($password) < 8) {
        $errors[] = "Mật khẩu phải có ít nhất 8 ký tự";
    }

    // Kiểm tra mật khẩu xác nhận
    if ($password !== $confirmPassword) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }

    // Kiểm tra yêu cầu mật khẩu
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $errors[] = "Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt";
    }

    // Kiểm tra điều khoản
    if (!$termsChecked) {
        $errors[] = "Vui lòng đồng ý với điều khoản dịch vụ";
    }

    // Kiểm tra email đã tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        error_log("Lỗi chuẩn bị truy vấn email: " . $conn->error);
        $errors[] = "Lỗi hệ thống khi kiểm tra email";
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email đã được sử dụng";
        }
        $stmt->close();
    }

    // Kiểm tra username đã tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Lỗi chuẩn bị truy vấn username: " . $conn->error);
        $errors[] = "Lỗi hệ thống khi kiểm tra tên đăng nhập";
    } else {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Tên đăng nhập đã được sử dụng";
        }
        $stmt->close();
    }

    // Nếu không có lỗi, tiến hành đăng ký
    if (empty($errors)) {
        try {
            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                throw new Exception("Lỗi khi mã hóa mật khẩu");
            }

            // Chuẩn bị và thực thi câu lệnh SQL
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, `password`) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị truy vấn INSERT: " . $conn->error);
            }

            $stmt->bind_param("sssss", $firstName, $lastName, $email, $username, $hashedPassword);
            if ($stmt->execute()) {
                error_log("Đăng ký thành công cho user: $username");
                // Chuyển hướng về Register.html với thông báo thành công
                header("Location: ../catalog/Register.html?success=true");
                exit();
            } else {
                throw new Exception("Lỗi thực thi INSERT: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Lỗi đăng ký: " . $e->getMessage());
            // Chuyển hướng về Register.html với thông báo lỗi
            header("Location: ../catalog/Register.html?message=" . urlencode("Lỗi khi đăng ký: " . $e->getMessage()));
            exit();
        }
    } else {
        // Chuyển hướng về Register.html với thông báo lỗi
        header("Location: ../catalog/Register.html?message=" . urlencode(implode(", ", $errors)));
        exit();
    }
} else {
    // Nếu không phải POST, chuyển hướng với thông báo lỗi
    header("Location: ../catalog/Register.html?message=" . urlencode("Yêu cầu không hợp lệ"));
    exit();
}

// Đóng kết nối
$conn->close();
?>
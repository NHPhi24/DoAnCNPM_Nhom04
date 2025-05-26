<?php
session_start();

$connect_path = '../backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

if (!isset($_SESSION['payment_data'])) {
    header("Location: movie.php");
    exit();
}

// Lấy thông tin tài khoản đăng nhập từ bảng users
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.'); window.location.href = 'login.php';</script>";
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$stmt_user = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $customer_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    $email = htmlspecialchars($user['email']);
} else {
    $customer_name = 'Khách hàng';
    $email = 'Không có email';
}
$stmt_user->close();

$payment_data = $_SESSION['payment_data'];
$title = htmlspecialchars($payment_data['title']);
$movie_name = htmlspecialchars($payment_data['movie_name']);
$movie_id = (int)$payment_data['movie_id'];
$showtime_id = (int)$payment_data['showtime_id'];
$show_date = htmlspecialchars($payment_data['show_date']);
$show_time = htmlspecialchars($payment_data['show_time']);
$theater = htmlspecialchars($payment_data['theater']);
$total_amount = htmlspecialchars($payment_data['total_amount']);
$seats = htmlspecialchars($payment_data['seats']);
$seats_to_book = $payment_data['seats_to_book'];
$user_id = (int)$payment_data['user_id'];

// Xử lý thanh toán 
$show_success_message = false;
if (isset($_POST['confirm_payment'])) {
    foreach ($seats_to_book as $seat) {
        $seat_number = $seat['seat_number'];
        $ticket_price = $seat['ticket_price'];

        // Kiểm tra ghế có còn trống không
        $stmt_check_seat = $conn->prepare("SELECT * FROM tickets WHERE showtime_id = ? AND seat_number = ? AND status = 'completed'");
        $stmt_check_seat->bind_param("is", $showtime_id, $seat_number);
        $stmt_check_seat->execute();
        $seat_result = $stmt_check_seat->get_result();

        if ($seat_result->num_rows > 0) {
            echo "<script>alert('Ghế $seat_number đã được đặt, vui lòng chọn lại!'); window.location.href = 'movie.php?movie_id=$movie_id';</script>";
            $stmt_check_seat->close();
            exit();
        }
        $stmt_check_seat->close();

        // Lưu vé vào bảng tickets
        $stmt_book = $conn->prepare("INSERT INTO tickets (user_id, movie_id, showtime_id, seat_number, ticket_price, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt_book->bind_param("iiisd", $user_id, $movie_id, $showtime_id, $seat_number, $ticket_price);

        if ($stmt_book->execute()) {
            // Cập nhật trạng thái ghế thành "đã đặt"
            $stmt_update_seat = $conn->prepare("UPDATE seats s JOIN showtimes st ON s.screen_id = st.screen_id SET s.is_available = 0 WHERE st.showtime_id = ? AND s.seat_number = ?");
            $stmt_update_seat->bind_param("is", $showtime_id, $seat_number);
            $stmt_update_seat->execute();
            $stmt_update_seat->close();
        } else {
            echo "<script>alert('Lỗi khi đặt vé cho ghế $seat_number: " . $conn->error . "');</script>";
        }
        $stmt_book->close();
    }

    unset($_SESSION['payment_data']);
    $show_success_message = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2d1b4e;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            color: #000;
            padding: 20px;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            color: #ff1493;
            margin-bottom: 20px;
        }
        .payment-section {
            display: flex;
            gap: 20px;
        }
        .payment-info, .payment-methods {
            flex: 1;
            padding: 15px;
            background-color: #fff;
        }
        .payment-info {
            border: 2px solid #ff1493;
        }
        .payment-info h2 {
            color: #ff1493;
            margin-bottom: 10px;
        }
        .payment-info p {
            margin: 5px 0;
        }
        .payment-methods {
            border: 2px solid #ff1493;
        }
        .payment-methods h2 {
            color: #ff1493;
            margin-bottom: 10px;
        }
        .payment-method-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }
        .paypal-btn {
            background-color: #ffc107;
            color: #000;
        }
        .momo-btn {
            background-color: #ff1493;
            color: #fff;
        }
        .continue-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .continue-btn:hover {
            background-color: #0056b3;
        }

        /* Modal thông báo thành công */
        .success-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #28a745;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
            font-size: 18px;
            min-width: 300px;
        }
        .success-modal p {
            margin: 0;
        }
        .success-modal.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1>THANH TOÁN</h1>
        <div class="payment-section">
            <div class="payment-info">
                <h2>THÔNG TIN THANH TOÁN</h2>
                <p><strong>Tên khách hàng:</strong> <?php echo $customer_name; ?></p>
                <p><strong>Email khách hàng:</strong> <?php echo $email; ?></p>
                <p><strong>Tên phim:</strong> <?php echo $movie_name; ?></p>
                <p><strong>Thời gian:</strong> <?php echo $show_date; ?></p>
                <p><strong>Suất chiếu:</strong> <?php echo $show_time; ?></p>
                <p><strong>Rạp:</strong> <?php echo $theater; ?></p>
                <p><strong>Ghế:</strong> <?php echo $seats; ?></p>
                <p><strong>Tổng tiền:</strong> <?php echo number_format($total_amount, 0, ',', '.') . ' VNĐ'; ?></p>
            </div>
            <div class="payment-methods">
                <h2>CHỌN PHƯƠNG THỨC THANH TOÁN</h2>
                <button class="payment-method-btn paypal-btn">PayPal</button>
                <button class="payment-method-btn momo-btn">Thanh toán MoMo</button>
                <form method="POST">
                    <input type="hidden" name="confirm_payment" value="1">
                    <button type="submit" class="continue-btn">Tiếp tục</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal thông báo thành công -->
    <div id="successModal" class="success-modal">
        <p>Mua vé thành công! Cảm ơn bạn đã đặt vé.</p>
    </div>

    <script>
        // Hiển thị modal thông báo nếu thanh toán thành công
        <?php if ($show_success_message): ?>
            const successModal = document.getElementById('successModal');
            successModal.classList.add('show');
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 3000); 
        <?php endif; ?>
    </script>
</body>
</html>
<?php
$conn->close();
?>
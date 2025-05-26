<?php
// Kiểm tra quyền truy cập của admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Bạn không có quyền truy cập trang này. Vui lòng đăng nhập với tài khoản admin.'); window.location.href = 'login.php';</script>";
    exit();
}

// Kết nối cơ sở dữ liệu
$connect_path = './backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Xử lý xóa vé
$show_success_message = false;
if (isset($_GET['delete_ticket'])) {
    $ticket_id = (int)$_GET['delete_ticket'];

    // Xóa vé
    $stmt_delete = $conn->prepare("DELETE FROM tickets WHERE ticket_id = ?");
    $stmt_delete->bind_param("i", $ticket_id);
    if ($stmt_delete->execute()) {
        $show_success_message = true;
    } else {
        echo "<script>alert('Lỗi khi xóa vé: " . $conn->error . "');</script>";
    }
    $stmt_delete->close();
}

// Xử lý tìm kiếm
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search_query)) {
    $search_query = '%' . $search_query . '%';
    $search_condition = "WHERE (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR m.title LIKE ?)";
}

// Lấy danh sách vé
$sql = "SELECT t.ticket_id, t.user_id, t.movie_id, t.showtime_id, t.seat_number, t.ticket_price, t.status,
               CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
               m.title AS movie_title,
               s.show_date, s.show_time
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        JOIN movies m ON t.movie_id = m.movie_id
        JOIN showtimes s ON t.showtime_id = s.showtime_id
        $search_condition
        ORDER BY t.ticket_id DESC";

if (!empty($search_query)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $tickets_result = $stmt->get_result();
} else {
    $tickets_result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Vé - Admin</title>
    <style>

        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            color: #000;
            padding: 65px;
            border-radius: 10px;
            font-size: 60px;
        }
        h1 {
            text-align: center;
            color: #ff1493;
            margin-bottom: 20px;
        }
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-bar button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        .search-bar button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 25px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 15px;
        }
        th {
            background-color: #ff1493;
            color: #fff;
        }
        td {
            background-color: #f9f9f9;
        }
        .action-buttons a {
            padding: 5px 10px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }
        .action-buttons a:hover {
            background-color: #c82333;
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
    <div class="admin-container">
        <h1>QUẢN LÝ VÉ</h1>

        <!-- Ô tìm kiếm -->
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm theo tên khách hàng hoặc tên phim..." value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <!-- Bảng danh sách vé -->
        <table>
            <thead>
                <tr>
                    <th>ID Vé</th>
                    <th>Tên Khách Hàng</th>
                    <th>Tên Phim</th>
                    <th>Suất Chiếu</th>
                    <th>Ghế</th>
                    <th>Giá Vé</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tickets_result->num_rows > 0): ?>
                    <?php while ($ticket = $tickets_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['movie_title']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($ticket['show_date'])) . ' ' . date('H:i', strtotime($ticket['show_time']))); ?></td>
                            <td><?php echo htmlspecialchars($ticket['seat_number']); ?></td>
                            <td><?php echo number_format($ticket['ticket_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                            <td class="action-buttons">
                                <a href="admin_tickets.php?delete_ticket=<?php echo $ticket['ticket_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa vé này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Không có vé nào để hiển thị.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal thông báo thành công -->
    <div id="successModal" class="success-modal">
        <p>Xóa vé thành công!</p>
    </div>

    <script>
        // Hiển thị modal thông báo nếu xóa vé thành công
        <?php if ($show_success_message): ?>
            const successModal = document.getElementById('successModal');
            successModal.classList.add('show');
            setTimeout(() => {
                successModal.classList.remove('show');
            }, 3000); // Ẩn modal sau 3 giây
        <?php endif; ?>
    </script>
    <?php include "./footer.php"; ?>
</body>
</html>
<?php
if (!empty($search_query)) {
    $stmt->close();
}
$conn->close();
?>
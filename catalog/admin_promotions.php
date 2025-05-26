<?php

// Kiểm tra quyền truy cập của admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Bạn không có quyền truy cập trang này. Vui lòng đăng nhập với tài khoản admin.'); window.location.href = 'login.php';</script>";
    exit();
}

$connect_path = './backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Xử lý thêm khuyến mãi
$show_success_message = false;
if (isset($_POST['add_promotion'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $image_url = htmlspecialchars($_POST['image_url']);

    $stmt = $conn->prepare("INSERT INTO promotions (title, description, start_date, end_date, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $image_url);
    if ($stmt->execute()) {
        $show_success_message = true;
    } else {
        echo "<script>alert('Lỗi khi thêm khuyến mãi: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Xử lý chỉnh sửa khuyến mãi
if (isset($_POST['edit_promotion'])) {
    $promotion_id = (int)$_POST['promotion_id'];
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $image_url = htmlspecialchars($_POST['image_url']);

    $stmt = $conn->prepare("UPDATE promotions SET title = ?, description = ?, start_date = ?, end_date = ?, image_url = ? WHERE promotion_id = ?");
    $stmt->bind_param("sssssi", $title, $description, $start_date, $end_date, $image_url, $promotion_id);
    if ($stmt->execute()) {
        $show_success_message = true;
    } else {
        echo "<script>alert('Lỗi khi chỉnh sửa khuyến mãi: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Xử lý xóa khuyến mãi
if (isset($_GET['delete_promotion'])) {
    $promotion_id = (int)$_GET['delete_promotion'];

    $stmt = $conn->prepare("DELETE FROM promotions WHERE promotion_id = ?");
    $stmt->bind_param("i", $promotion_id);
    if ($stmt->execute()) {
        $show_success_message = true;
    } else {
        echo "<script>alert('Lỗi khi xóa khuyến mãi: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Lấy danh sách khuyến mãi
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search_query)) {
    $search_query = '%' . $search_query . '%';
    $search_condition = "WHERE title LIKE ?";
}

$sql = "SELECT * FROM promotions $search_condition ORDER BY promotion_id DESC";
if (!empty($search_query)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_query);
    $stmt->execute();
    $promotions_result = $stmt->get_result();
} else {
    $promotions_result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khuyến Mãi - Admin</title>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            color: #000;
            padding: 65px;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            color: #ff1493;
            margin-bottom: 20px;
            font-size: 35px;
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
        .add-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .add-button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
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
        .action-buttons {
            display: flex;
        }
        .action-buttons a {
            padding: 5px 10px;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 3px;
        }
        .edit-btn {
            background-color: #007bff;
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        /* Modal thêm/chỉnh sửa */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            font-size: 20px;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 60%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            font-size: 20px;
        }
        .modal-content h2 {
            margin-top: 0;
            color: #ff1493;
            font-size: 15px;
        }
        .modal-content label {
            display: block;
            margin: 10px 0 5px;
            color: black;
        }
        label {
            color: black;
        }
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 10px;
        }
        .modal-content button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 20px;
        }
        .modal-content button:hover {
            background-color: #0056b3;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
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
        <h1>QUẢN LÝ KHUYẾN MÃI</h1>

        <!-- Ô tìm kiếm và nút thêm -->
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm theo tiêu đề khuyến mãi..." value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                <button type="submit">Tìm kiếm</button>
            </form>
            <br><br>
            <a href="#" class="add-button" onclick="openAddModal()">Thêm Khuyến Mãi</a>
        </div>

        <!-- Bảng danh sách khuyến mãi -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu Đề</th>
                    <th>Mô Tả</th>
                    <th>Thời Gian Áp Dụng</th>
                    <th>Hình Ảnh</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($promotions_result->num_rows > 0): ?>
                    <?php while ($promotion = $promotions_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($promotion['promotion_id']); ?></td>
                            <td><?php echo htmlspecialchars($promotion['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($promotion['description'], 0, 50)) . (strlen($promotion['description']) > 50 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($promotion['start_date'])) . ' - ' . date('d/m/Y', strtotime($promotion['end_date']))); ?></td>
                            <td><?php echo htmlspecialchars($promotion['image_url'] ?: 'Không có'); ?></td>
                            <td class="action-buttons">
                                <a href="#" class="edit-btn" onclick="openEditModal(<?php echo json_encode($promotion); ?>)">Sửa</a>
                                <a href="admin_promotions.php?delete_promotion=<?php echo $promotion['promotion_id']; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Không có khuyến mãi nào để hiển thị.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal thêm khuyến mãi -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
            <h2>Thêm Khuyến Mãi</h2>
            <form method="POST">
                <label for="title">Tiêu Đề:</label>
                <input type="text" name="title" id="title" required>
                <label for="description">Mô Tả:</label>
                <textarea name="description" id="description" required></textarea>
                <label for="start_date">Ngày Bắt Đầu:</label>
                <input type="date" name="start_date" id="start_date" required value="<?php echo date('Y-m-d'); ?>">
                <label for="end_date">Ngày Kết Thúc:</label>
                <input type="date" name="end_date" id="end_date" required value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                <label for="image_url">Đường Dẫn Hình Ảnh:</label>
                <input type="text" name="image_url" id="image_url">
                <button type="submit" name="add_promotion">Thêm</button>
            </form>
        </div>
    </div>

    <!-- Modal chỉnh sửa khuyến mãi -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h2>Chỉnh Sửa Khuyến Mãi</h2>
            <form method="POST">
                <input type="hidden" name="promotion_id" id="edit_promotion_id">
                <label for="edit_title">Tiêu Đề:</label>
                <input type="text" name="title" id="edit_title" required>
                <label for="edit_description">Mô Tả:</label>
                <textarea name="description" id="edit_description" required></textarea>
                <label for="edit_start_date">Ngày Bắt Đầu:</label>
                <input type="date" name="start_date" id="edit_start_date" required>
                <label for="edit_end_date">Ngày Kết Thúc:</label>
                <input type="date" name="end_date" id="edit_end_date" required>
                <label for="edit_image_url">Đường Dẫn Hình Ảnh:</label>
                <input type="text" name="image_url" id="edit_image_url">
                <button type="submit" name="edit_promotion">Lưu</button>
            </form>
        </div>
    </div>

    <!-- Modal thông báo thành công -->
    <div id="successModal" class="success-modal">
        <p>Thao tác thành công!</p>
    </div>

    <script>
        // Mở modal thêm
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        // Mở modal chỉnh sửa
        function openEditModal(promotion) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_promotion_id').value = promotion.promotion_id;
            document.getElementById('edit_title').value = promotion.title;
            document.getElementById('edit_description').value = promotion.description;
            document.getElementById('edit_start_date').value = promotion.start_date;
            document.getElementById('edit_end_date').value = promotion.end_date;
            document.getElementById('edit_image_url').value = promotion.image_url || '';
        }

        // Đóng modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Hiển thị modal thông báo nếu thao tác thành công
        <?php if ($show_success_message): ?>
            const successModal = document.getElementById('successModal');
            successModal.classList.add('show');
            setTimeout(() => {
                successModal.classList.remove('show');
                window.location.href = 'admin_promotions.php';
            }, 3000); // Tải lại trang sau 3 giây
        <?php endif; ?>

        // Đóng modal khi nhấp bên ngoài
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                closeModal('addModal');
                closeModal('editModal');
            }
        }
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
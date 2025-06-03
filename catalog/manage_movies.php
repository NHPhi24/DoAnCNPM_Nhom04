<?php

$connect_path = './backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Xử lý thêm phim mới
if (isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = (int)$_POST['duration'];
    $director = $_POST['director'];
    $cast = $_POST['cast'];
    $description = $_POST['description'];
    $release_date = $_POST['release_date'];
    $rating = (float)$_POST['rating'];
    $img_url = $_POST['img_url'];

    $stmt = $conn->prepare("INSERT INTO Movies (title, genre, duration, director, cast, description, release_date, rating, img_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssssd", $title, $genre, $duration, $director, $cast, $description, $release_date, $rating, $img_url);
    
    if ($stmt->execute()) {
        $success_message = "Thêm phim mới thành công!";
    } else {
        $error_message = "Lỗi khi thêm phim: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý chỉnh sửa phim
if (isset($_POST['edit_movie'])) {
    $movie_id = (int)$_POST['movie_id'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = (int)$_POST['duration'];
    $director = $_POST['director'];
    $cast = $_POST['cast'];
    $description = $_POST['description'];
    $release_date = $_POST['release_date'];
    $rating = (float)$_POST['rating'];
    $img_url = $_POST['img_url'];

    $stmt = $conn->prepare("UPDATE Movies SET title = ?, genre = ?, duration = ?, director = ?, cast = ?, description = ?, release_date = ?, rating = ?, img_url = ? WHERE movie_id = ?");
    $stmt->bind_param("ssisssssdi", $title, $genre, $duration, $director, $cast, $description, $release_date, $rating, $img_url, $movie_id);
    
    if ($stmt->execute()) {
        $success_message = "Cập nhật thông tin phim thành công!";
    } else {
        $error_message = "Lỗi khi cập nhật phim: " . $conn->error;
    }
    $stmt->close();
}

// Xử lý thêm lịch chiếu
if (isset($_POST['add_showtime'])) {
    $movie_id = (int)$_POST['movie_id'];
    $screen_id = (int)$_POST['screen_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $ticket_price = (float)$_POST['ticket_price'];

    $stmt = $conn->prepare("INSERT INTO Showtimes (movie_id, screen_id, show_date, show_time, ticket_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $movie_id, $screen_id, $show_date, $show_time, $ticket_price);
    
    if ($stmt->execute()) {
        $success_message = "Thêm lịch chiếu thành công!";
    } else {
        $error_message = "Lỗi khi thêm lịch chiếu: " . $conn->error;
    }
    $stmt->close();
}

// Lấy danh sách phim
$movies_query = "SELECT * FROM Movies ORDER BY release_date DESC";
$movies_result = $conn->query($movies_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/css/index.css">
    <link rel="stylesheet" href="../Assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Quản lý phim</title>
    <style>
        .admin-container {
            width: 95%;
            max-width: 1400px;
            margin: 30px 30px;
            font-family: Arial, sans-serif;
            font-size: 20px;
        }
        h1 {
            font-size: 2.5em;
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }
        .movies-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .movies-table th, .movies-table td {
            border: 1px solid #e0e0e0;
            padding: 15px;
            text-align: left;
            font-size: 15px;
            background-color: #fff;
        }
        .movies-table th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        .movies-table tr {
            transition: background-color 0.3s;
        }
        .movies-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .movies-table tr:hover {
            background-color: #e9ecef;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            transition: background-color 0.3s;
        }
        .btn-add {
            background-color: #28a745;
            color: white;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .btn-edit {
            background-color: #007bff;
            color: white;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 30px;
            width: 60%;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-height: 90vh; /* Giới hạn chiều cao tối đa */
            overflow-y: auto; /* Thêm thanh kéo dọc khi cần */
        }
        .modal-content h2 {
            font-size: 15px;
            margin-bottom: 20px;
            color: #333;
        }
        .modal-content form {
            display: flex;
            flex-direction: column;
        }
        .modal-content label {
            font-size: 12px;
            margin: 10px 0 5px;
            color: #555;
        }
        .modal-content input,
        .modal-content textarea,
        .modal-content select {
            padding: 10px;
            font-size: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        .modal-content textarea {
            height: 100px;
            resize: vertical;
        }
        .modal-content button {
            margin-top: 10px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 10px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .movies-table th, .movies-table td {
                font-size: 0.9em;
                padding: 10px;
            }
            .modal-content {
                width: 90%;
                padding: 20px;
            }
            .movie-image {
                max-width: 20px;
                max-height: 30px;
            }
            .btn {
                padding: 8px 15px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Quản lý phim</h1>
        <button class="btn btn-add" onclick="openAddMovieModal()">Thêm phim mới</button>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <table class="movies-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>IMG</th>
                    <th>Thể loại</th>
                    <th>Thời lượng (phút)</th>
                    <th>Ngày phát hành</th>
                    <th>Đánh giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($movies_result->num_rows > 0): ?>
                    <?php while ($movie = $movies_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['movie_id']); ?></td>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($movie['img_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                     class="movie-image" 
                                     onerror="this.src='../Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo htmlspecialchars($movie['img_url']); ?> not found, fallback to movie2.webp');">
                            </td>
                            <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                            <td><?php echo htmlspecialchars($movie['duration']); ?></td>
                            <td><?php echo htmlspecialchars($movie['release_date']); ?></td>
                            <td><?php echo htmlspecialchars($movie['rating']); ?></td>
                            <td>
                                <button class="btn btn-edit" onclick='openEditMovieModal(<?php echo json_encode($movie); ?>)'>Chỉnh sửa</button>
                                <button class="btn btn-add" onclick="openAddShowtimeModal(<?php echo $movie['movie_id']; ?>)">Thêm lịch chiếu</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Không có phim nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal thêm phim mới -->
    <div id="addMovieModal" class="modal">
        <div class="modal-content">
            <h2>Thêm phim mới</h2>
            <form method="POST">
                <input type="hidden" name="add_movie" value="1">
                <label for="title">Tiêu đề:</label>
                <input type="text" name="title" required>
                
                <label for="genre">Thể loại:</label>
                <input type="text" name="genre" required>
                
                <label for="duration">Thời lượng (phút):</label>
                <input type="number" name="duration" required>
                
                <label for="director">Đạo diễn:</label>
                <input type="text" name="director" required>
                
                <label for="cast">Diễn viên:</label>
                <input type="text" name="cast" required>
                
                <label for="description">Mô tả:</label>
                <textarea name="description" required></textarea>
                
                <label for="release_date">Ngày phát hành:</label>
                <input type="date" name="release_date" required>
                
                <label for="rating">Đánh giá (0-10):</label>
                <input type="number" step="0.1" min="0" max="10" name="rating" required>
                
                <label for="img_url">URL hình ảnh:</label>
                <input type="text" name="img_url" placeholder="../...">
                
                <button type="submit" class="btn btn-add">Thêm phim</button>
                <button type="button" class="btn" onclick="closeAddMovieModal()">Đóng</button>
            </form>
        </div>
    </div>

    <!-- Modal chỉnh sửa phim -->
    <div id="editMovieModal" class="modal">
        <div class="modal-content">
            <h2>Chỉnh sửa phim</h2>
            <form method="POST">
                <input type="hidden" name="edit_movie" value="1">
                <input type="hidden" name="movie_id" id="edit_movie_id">
                
                <b><label for="title">Tiêu đề:</label></b>
                <input type="text" name="title" id="edit_title" required>
                
                <b><label for="genre">Thể loại:</label></b>
                <input type="text" name="genre" id="edit_genre" required>
                
                <b><label for="duration">Thời lượng (phút):</label></b>
                <input type="number" name="duration" id="edit_duration" required>
                
                <b><label for="director">Đạo diễn:</label></b>
                <input type="text" name="director" id="edit_director" required>
                
                <b><label for="cast">Diễn viên:</label></b>
                <input type="text" name="cast" id="edit_cast" required>
                
                <b><label for="description">Mô tả:</label></b>
                <textarea name="description" id="edit_description" required></textarea>
                
                <b><label for="release_date">Ngày phát hành:</label></b>
                <input type="date" name="release_date" id="edit_release_date" required>
                
                <b><label for="rating">Đánh giá (0-10):</label></b>
                <input type="number" step="0.1" min="0" max="10" name="rating" id="edit_rating" required>
                
                <b><label for="img_url">URL hình ảnh:</label></b>
                <input type="text" name="img_url" id="edit_img_url" placeholder=".../...">
                
                <button type="submit" class="btn btn-edit">Cập nhật</button>
                <button type="button" class="btn" onclick="closeEditMovieModal()">Đóng</button>
            </form>
        </div>
    </div>

    <!-- Modal thêm lịch chiếu -->
    <div id="addShowtimeModal" class="modal">
        <div class="modal-content">
            <h2>Thêm lịch chiếu</h2>
            <form method="POST">
                <input type="hidden" name="add_showtime" value="1">
                <input type="hidden" name="movie_id" id="showtime_movie_id">
                
                <label for="screen_id">Rạp chiếu:</label>
                <select name="screen_id" required>
                    <option value="1">Rạp 1</option>
                    <option value="2">Rạp 2</option>
                    <option value="3">Rạp 3</option>
                </select>
                
                <label for="show_date">Ngày chiếu:</label>
                <input type="date" name="show_date" required>
                
                <label for="show_time">Giờ chiếu:</label>
                <input type="time" name="show_time" required>
                
                <label for="ticket_price">Giá vé (VNĐ):</label>
                <input type="number" step="1000" name="ticket_price" required>
                
                <button type="submit" class="btn btn-add">Thêm lịch chiếu</button>
                <button type="button" class="btn" onclick="closeAddShowtimeModal()">Đóng</button>
            </form>
        </div>
    </div>

    <script>
        // Modal thêm phim mới
        function openAddMovieModal() {
            document.getElementById('addMovieModal').style.display = 'block';
        }
        function closeAddMovieModal() {
            document.getElementById('addMovieModal').style.display = 'none';
        }

        // Modal chỉnh sửa phim
        function openEditMovieModal(movie) {
            document.getElementById('edit_movie_id').value = movie.movie_id;
            document.getElementById('edit_title').value = movie.title;
            document.getElementById('edit_genre').value = movie.genre;
            document.getElementById('edit_duration').value = movie.duration;
            document.getElementById('edit_director').value = movie.director;
            document.getElementById('edit_cast').value = movie.cast;
            document.getElementById('edit_description').value = movie.description;
            document.getElementById('edit_release_date').value = movie.release_date;
            document.getElementById('edit_rating').value = movie.rating;
            document.getElementById('edit_img_url').value = movie.img_url || '';
            document.getElementById('editMovieModal').style.display = 'block';
        }
        function closeEditMovieModal() {
            document.getElementById('editMovieModal').style.display = 'none';
        }

        // Modal thêm lịch chiếu
        function openAddShowtimeModal(movie_id) {
            document.getElementById('showtime_movie_id').value = movie_id;
            document.getElementById('addShowtimeModal').style.display = 'block';
        }
        function closeAddShowtimeModal() {
            document.getElementById('addShowtimeModal').style.display = 'none';
        }

        // Đóng modal khi nhấp bên ngoài
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeAddMovieModal();
                closeEditMovieModal();
                closeAddShowtimeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
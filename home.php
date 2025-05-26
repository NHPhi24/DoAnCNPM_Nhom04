<?php
// Kiểm tra và yêu cầu file connect.php
$connect_path = './backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Khởi tạo query cơ bản để lấy tất cả phim
$sql = "SELECT movie_id, title, genre, release_date, img_url FROM Movies WHERE 1=1";
$params = array();

// Xử lý tìm kiếm theo tiêu đề (chỉ khi có tham số search)
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_query)) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$search_query%";
}

// Xử lý lọc theo thể loại (chỉ khi có tham số genre)
$genre_filter = isset($_GET['genre']) ? trim($_GET['genre']) : '';
if (!empty($genre_filter)) {
    // Chuẩn hóa giá trị thể loại để tìm kiếm
    if ($genre_filter === "Hài hước") {
        $genre_filter = "Hài";
    }
    $sql .= " AND genre LIKE ?";
    $params[] = "%$genre_filter%";
}

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div id="container">
    <div class="grid">
        <div class="container">
            <div class="container-left">
                <div class="title">
                    <p class="title-left live_movie">Tất cả phim</p>
                    <p class="title-right"><a href="">Xem tất cả</a></p>
                </div>
                <div class="content box-warp">
                    <?php
                    if ($result->num_rows > 0) {
                        // Hiển thị danh sách phim
                        while ($row = $result->fetch_assoc()) {
                            $movie_id = htmlspecialchars($row['movie_id']);
                            $title = htmlspecialchars($row['title']);
                            $genre = htmlspecialchars($row['genre']);
                            $release_date = htmlspecialchars($row['release_date']);
                            $img_url = htmlspecialchars($row['img_url'] ?? './Assets/images/movie2.webp');
                            $formatted_date = date('d/m/Y', strtotime($release_date));
                            ?>
                            <div class="movie">
                                <a href="./catalog/movie.php?movie_id=<?php echo $movie_id; ?>">
                                    <img src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>" onerror="this.src='./Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo $img_url; ?> not found, fallback to movie2.webp');">
                                </a>
                                <p><?php echo $genre; ?></p>
                                <p><?php echo $formatted_date; ?></p>
                                <h2 class="text-warp"><?php echo $title; ?></h2>
                            </div>
                            <?php
                        }
                    } else {
                        // Hiển thị thông báo nếu không có phim nào
                        echo '<p style="color: #aaa; text-align: center; width: 100%;">Không tìm thấy phim nào.</p>';
                    }

                    // Đóng kết nối
                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="container-right">
                <div class="title">
                    <p class="title-left">Khuyến mãi</p>
                    <p class="title-right"><a href="">Xem tất cả</a></p>
                </div>
                <div class="promotion">
                    <a href=""><img src="https://s3-hni.sds.vnpaycloud.vn/minio-vbacms/20250113/bung-no-nam-moi-voi-chuong-trinh-phim-hay-uu-dai-hot-dau-nam-moi-agribank_18686326969213952.jpg" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
                    <a href=""><img src="https://cdn-www.vinid.net/2020/06/020b6aec-12-06-2020_skydeal_bannerweb_1920x1080.jpg" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
                    <a href=""><img src="https://pixelcinema.vn/Areas/Admin/Content/Fileuploads/images/TH%E1%BB%A9%202%20t%E1%BA%B7ng%20b%E1%BA%AFp%402x.png" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
                </div>
                <div class="next-page">
                    <div class="circle"></div>
                    <div class="circle"></div>
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Kiểm tra và yêu cầu file connect.php
$connect_path = 'backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', strtotime('2025-04-25')); // Mặc định ngày đầu tiên

// Lấy danh sách ngày độc nhất từ Showtimes
$stmt_dates = $conn->prepare("SELECT DISTINCT show_date FROM Showtimes WHERE show_date >= CURDATE() ORDER BY show_date");
if (!$stmt_dates) {
    die("Lỗi truy vấn ngày: " . $conn->error);
}
$stmt_dates->execute();
$dates_result = $stmt_dates->get_result();

// Lấy danh sách phim cho ngày được chọn
$stmt_films = $conn->prepare("SELECT m.movie_id, m.title, m.genre, m.duration, m.release_date, m.img_url, s.show_time 
                              FROM Movies m 
                              JOIN Showtimes s ON m.movie_id = s.movie_id 
                              WHERE s.show_date = ? 
                              ORDER BY s.show_time");
if (!$stmt_films) {
    die("Lỗi truy vấn phim: " . $conn->error);
}
$stmt_films->bind_param("s", $selected_date);
$stmt_films->execute();
$films_result = $stmt_films->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Assets/css/index.css">
    <link rel="stylesheet" href="Assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="Assets/css/login.css">
    <link rel="stylesheet" href="Assets/css/resgistion.css">
    <link rel="stylesheet" href="Assets/css/scheduleFilm.css">
    <title>Movies</title>
</head>

<body>
    <div id="content">
        <div id="container">
            <div class="grid">
                <div class="container">
                    <h1>Phim đang chiếu</h1>
                    <div class="FilmDay">
                        <?php
                        if ($dates_result) {
                            while ($date = $dates_result->fetch_assoc()) {
                                $formatted_date = date('d-m-Y', strtotime($date['show_date']));
                                $is_active = $date['show_date'] === $selected_date ? 'active' : '';
                                echo "<button class='btn btn_sch $is_active' data-date='{$date['show_date']}'>
                                        <p>$formatted_date</p>
                                      </button>";
                            }
                        } else {
                            echo '<p style="color: red;">Không có ngày lịch chiếu để hiển thị.</p>';
                        }
                        ?>
                    </div>
                    <div class="note"><span><b>Lưu ý</b>: Khán giả dưới 13 tuổi chỉ chọn suất chiếu kết thúc trước 22h và Khán giả dưới 16 tuổi chỉ chọn suất chiếu kết thúc trước 23h.</span></div>
                    <div class="content2">
                        <?php
                        if ($films_result && $films_result->num_rows > 0) {
                            while ($film = $films_result->fetch_assoc()) {
                                $movie_id = htmlspecialchars($film['movie_id']);
                                $title = htmlspecialchars($film['title']);
                                $genre = htmlspecialchars($film['genre']);
                                $duration = htmlspecialchars($film['duration']);
                                $release_date = date('d/m/Y', strtotime($film['release_date']));
                                $img_url = htmlspecialchars($film['img_url'] ?? '../Assets/images/movie2.webp');
                                $show_time = date('H:i', strtotime($film['show_time']));
                                ?>
                                <div class="filmSch">
                                    <img src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>" onerror="this.src='../Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo $img_url; ?> not found, fallback to movie2.webp');">
                                    <div class="filmSch-right">
                                        <div class="category">
                                            <p><?php echo $genre; ?></p>
                                            <p><?php echo $duration; ?>p</p>
                                        </div>
                                        <h1><a href="../catalog/movie.php?movie_id=<?php echo $movie_id; ?>" style="text-decoration: none; color: inherit;"><?php echo $title; ?></a></h1>
                                        <p>Xuất xứ: <?php echo $release_date; ?></p>
                                        <p>P phim phổ biến với mọi độ tuổi</p>
                                        <h3>Lịch chiếu</h3>
                                        <button class="btn Lichchieu"><?php echo $show_time; ?></button>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p style="color: #aaa; text-align: center;">Không có phim nào chiếu vào ngày này.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        const buttons = document.querySelectorAll('.btn_sch');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const date = button.getAttribute('data-date');
                window.location.href = `scheduleFilm.php?date=${date}`;
            });
        });

        // Kích hoạt nút mặc định
        const activeButton = document.querySelector('.btn_sch.active');
        if (activeButton) {
            activeButton.classList.add('active');
        }
    </script>
</body>

</html>
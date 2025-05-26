<?php

$connect_path = 'backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Ngày hiện tại và 7 ngày sau 
$current_date = date('Y-m-d'); // 26/05/2025
$end_date = date('Y-m-d', strtotime($current_date . ' +7 days')); // 02/06/2025
$selected_date = isset($_GET['date']) ? $_GET['date'] : $current_date;

// Lấy danh sách ngày độc nhất từ Showtimes trong vòng 1 tuần
$stmt_dates = $conn->prepare("SELECT DISTINCT show_date FROM Showtimes WHERE show_date BETWEEN ? AND ? ORDER BY show_date");
$stmt_dates->bind_param("ss", $current_date, $end_date);
$stmt_dates->execute();
$dates_result = $stmt_dates->get_result();

// Lấy danh sách phim cho ngày được chọn
$stmt_films = $conn->prepare("SELECT m.movie_id, m.title, m.genre, m.duration, m.release_date, m.img_url, s.showtime_id, s.show_date, s.show_time, s.ticket_price, s.screen_id 
                              FROM Movies m 
                              JOIN Showtimes s ON m.movie_id = s.movie_id 
                              WHERE s.show_date = ? 
                              ORDER BY s.show_time");
$stmt_films->bind_param("s", $selected_date);
$stmt_films->execute();
$films_result = $stmt_films->get_result();

// Xử lý đặt vé
if (isset($_POST['book_ticket'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo "<script>alert('Vui lòng đăng nhập để mua vé!'); window.location.href = 'login.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $showtime_id = (int)$_POST['showtime_id'];
    $movie_id = (int)$_POST['movie_id'];
    $seat_number = $_POST['seat_number'];
    $base_price = (float)$_POST['ticket_price'];
    $seat_type = $_POST['seat_type'];

    $showtime = $conn->query("SELECT show_date, show_time FROM Showtimes WHERE showtime_id = $showtime_id")->fetch_assoc();
    $show_date = $showtime['show_date'];
    $show_time = strtotime($showtime['show_time']);
    $day_of_week = date('N', strtotime($show_date));
    $is_weekday = ($day_of_week >= 1 && $day_of_week <= 5);
    $hour = date('H', $show_time);
    $is_before_12pm = ($hour < 12);
    $is_before_11pm = ($hour >= 17 && $hour < 23);

    $ticket_price = $base_price;
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
        $ticket_price = 55000;
    } elseif (isset($_SESSION['role']) && in_array($_SESSION['role'], ['child', 'elder', 'disabled', 'hardship'])) {
        $ticket_price *= 0.8;
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'severe_disabled') {
        $ticket_price *= 0.5;
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'free') {
        $ticket_price = 0;
    } else {
        if ($is_weekday) {
            if ($is_before_12pm) $ticket_price = ($seat_type === 'standard') ? 55000 : ($seat_type === 'vip' ? 65000 : 140000);
            elseif ($is_before_11pm) $ticket_price = ($seat_type === 'standard') ? 70000 : ($seat_type === 'vip' ? 75000 : 160000);
            else $ticket_price = ($seat_type === 'standard') ? 65000 : ($seat_type === 'vip' ? 70000 : 150000);
        } else {
            if ($is_before_12pm) $ticket_price = ($seat_type === 'standard') ? 70000 : ($seat_type === 'vip' ? 80000 : 170000);
            elseif ($is_before_11pm) $ticket_price = ($seat_type === 'standard') ? 80000 : ($seat_type === 'vip' ? 85000 : 180000);
            else $ticket_price = ($seat_type === 'standard') ? 75000 : ($seat_type === 'vip' ? 80000 : 170000);
        }
    }

    $stmt_check_seat = $conn->prepare("SELECT * FROM Tickets WHERE showtime_id = ? AND seat_number = ? AND status = 'completed'");
    $stmt_check_seat->bind_param("is", $showtime_id, $seat_number);
    $stmt_check_seat->execute();
    $seat_result = $stmt_check_seat->get_result();

    if ($seat_result->num_rows > 0) {
        echo "<script>alert('Ghế này đã được đặt, vui lòng chọn ghế khác!');</script>";
    } else {
        $stmt_book = $conn->prepare("INSERT INTO Tickets (user_id, movie_id, showtime_id, seat_number, ticket_price, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt_book->bind_param("iiisd", $user_id, $movie_id, $showtime_id, $seat_number, $ticket_price);
        if ($stmt_book->execute()) {
            $stmt_update_seat = $conn->prepare("UPDATE Seats s JOIN Showtimes st ON s.screen_id = st.screen_id SET s.is_available = 0 WHERE st.showtime_id = ? AND s.seat_number = ?");
            $stmt_update_seat->bind_param("is", $showtime_id, $seat_number);
            $stmt_update_seat->execute();
            $stmt_update_seat->close();

            echo "<script>alert('Đặt vé thành công!'); window.location.href = 'scheduleFilm.php?date=$selected_date';</script>";
        } else {
            echo "<script>alert('Lỗi khi đặt vé: " . $conn->error . "');</script>";
        }
        $stmt_book->close();
    }
    $stmt_check_seat->close();
}
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
    <style>
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.6); 
            z-index: 1000; 
        }
        .modal-content { 
            background-color: white; 
            margin: 5% auto; 
            padding: 30px; 
            width: 60%; 
            max-width: 700px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.2); 
            max-height: 80vh; 
            overflow-y: auto; 
        }
        .modal-content h2 { 
            font-size: 1.8em; 
            margin-bottom: 20px; 
            color: #333; 
        }
        .btn { 
            padding: 10px 20px; 
            margin: 5px; 
            cursor: pointer; 
            border: none; 
            border-radius: 5px; 
            font-size:5px; 
            transition: background-color 0.3s; 
        }
        .btn-confirm { 
            background-color: #007bff; 
            color: white; 
        }
        .btn-confirm:hover { 
            background-color: #0056b3; 
        }
        .btn-close { 
            background-color: #dc3545; 
            color: white; 
        }
        .btn-close:hover { 
            background-color: #c82333; 
        }
        .ticket-info p { 
            font-size: 1.1em; margin: 5px 0; 
        }
        .Lichchieu { 
            margin: 15px; 
            padding: 5px 20px 5px 10px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 10px;
        }

        .Lichchieu:hover { 
            background-color: #0056b3; 
        }
        .content2{ 
            gap: 20px
        }
    </style>
</head>
<body>
    <?php include "./header.php"; ?>

    <div id="content">
        <div id="container">
            <div class="grid">
                <div class="container">
                    <h1>Phim đang chiếu</h1>
                    <div class="FilmDay">
                        <?php
                        if ($dates_result->num_rows > 0) {
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
                        if ($films_result->num_rows > 0) {
                            while ($film = $films_result->fetch_assoc()) {
                                $movie_id = htmlspecialchars($film['movie_id']);
                                $showtime_id = htmlspecialchars($film['showtime_id']);
                                $title = htmlspecialchars($film['title']);
                                $genre = htmlspecialchars($film['genre']);
                                $duration = htmlspecialchars($film['duration']);
                                $release_date = date('d/m/Y', strtotime($film['release_date']));
                                $img_url = htmlspecialchars($film['img_url'] ?? '../Assets/images/movie2.webp');
                                $show_time = date('H:i', strtotime($film['show_time']));
                                $show_date = $film['show_date'];
                                $ticket_price = htmlspecialchars($film['ticket_price']);
                                $screen_id = $film['screen_id'];
                                ?>
                                <div class="filmSch">
                                    <img src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>" onerror="this.src='../Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo $img_url; ?> not found, fallback to movie2.webp');">
                                    <div class="filmSch-right">
                                        <div class="category">
                                            <p><?php echo $genre; ?></p>
                                            <p><?php echo $duration; ?>p</p>
                                        </div>
                                        <h1><a href="./catalog/movie.php?movie_id=<?php echo $movie_id; ?>" style="text-decoration: none; color: inherit;"><?php echo $title; ?></a></h1>
                                        <p>Xuất xứ: <?php echo $release_date; ?></p>
                                        <p>Phim phổ biến với mọi độ tuổi</p>
                                        <h3>Lịch chiếu</h3>
                                        <button class="btn Lichchieu" onclick="openSeatModal(<?php echo $movie_id; ?>, <?php echo $showtime_id; ?>, '<?php echo $title; ?>', '<?php echo $show_date; ?>', '<?php echo $show_time; ?>', <?php echo $ticket_price; ?>, '<?php echo $screen_id; ?>')">
                                            <?php echo $show_time; ?>
                                        </button>
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

    <?php include "./footer.php"; ?>

</body>
</html>
<?php
$stmt_dates->close();
$stmt_films->close();
$conn->close();
?>
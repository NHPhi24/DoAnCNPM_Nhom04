<?php
session_start();

$connect_path = '../backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

if ($movie_id <= 0) {
    echo 'Phim không tồn tại.';
    exit();
}

$stmt = $conn->prepare("SELECT title, genre, duration, director, cast, description, release_date, rating, img_url FROM movies WHERE movie_id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $movie = $result->fetch_assoc();
    $title = htmlspecialchars($movie['title']);
    $genre = htmlspecialchars($movie['genre']);
    $duration = htmlspecialchars($movie['duration']);
    $director = htmlspecialchars($movie['director']);
    $cast = htmlspecialchars($movie['cast']);
    $description = htmlspecialchars($movie['description']);
    $release_date = htmlspecialchars($movie['release_date']);
    $formatted_date = date('d/m/Y', strtotime($release_date));
    $rating = htmlspecialchars($movie['rating']);
    $img_url = htmlspecialchars($movie['img_url'] ?? '../Assets/images/movie2.webp');
} else {
    echo 'Phim không tồn tại.';
    exit();
}

// Lấy danh sách ngày chiếu từ 26/05/2025 trở đi
$stmt_dates = $conn->prepare("SELECT showtime_id, show_date, show_time, ticket_price, screen_id FROM showtimes WHERE movie_id = ? AND show_date >= '2025-05-26' ORDER BY show_date, show_time");
$stmt_dates->bind_param("i", $movie_id);
$stmt_dates->execute();
$showtimes_result = $stmt_dates->get_result();

// Xử lý đặt vé
if (isset($_POST['book_ticket'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo "<script>alert('Vui lòng đăng nhập để mua vé!'); window.location.href = 'login.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $showtime_id = (int)$_POST['showtime_id'];
    $movie_id = (int)$_POST['movie_id'];
    $seat_numbers = json_decode($_POST['seat_number'], true); // Mảng ghế
    $seat_types = json_decode($_POST['seat_type'], true); // Mảng loại ghế
    $base_price = (float)$_POST['ticket_price'];

    // Lấy thông tin suất chiếu để lưu vào session
    $stmt_showtime = $conn->prepare("SELECT show_date, show_time FROM showtimes WHERE showtime_id = ?");
    $stmt_showtime->bind_param("i", $showtime_id);
    $stmt_showtime->execute();
    $showtime_result = $stmt_showtime->get_result();
    $showtime_data = $showtime_result->fetch_assoc();
    $stmt_showtime->close();

    $total_amount = 0;
    $seats_to_book = [];

    foreach ($seat_numbers as $index => $seat_number) {
        $seat_type = $seat_types[$index];
        $ticket_price = $base_price;

        // Tính giá vé dựa trên loại ghế
        if ($seat_type === 'standard') {
            $ticket_price = 60000;
        } elseif ($seat_type === 'vip') {
            $ticket_price = 80000;
        } elseif ($seat_type === 'couple') {
            $ticket_price = 150000; // Giá cố định cho ghế đôi
        }

        // Ưu đãi (giữ nguyên logic cũ)
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
            $ticket_price = 55000;
        } elseif (isset($_SESSION['role']) && in_array($_SESSION['role'], ['child', 'elder', 'disabled', 'hardship'])) {
            $ticket_price *= 0.8;
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'severe_disabled') {
            $ticket_price *= 0.5;
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'free') {
            $ticket_price = 0;
        }

        // Kiểm tra ghế đã được đặt chưa
        $stmt_check_seat = $conn->prepare("SELECT * FROM tickets WHERE showtime_id = ? AND seat_number = ? AND status = 'completed'");
        $stmt_check_seat->bind_param("is", $showtime_id, $seat_number);
        $stmt_check_seat->execute();
        $seat_result = $stmt_check_seat->get_result();

        if ($seat_result->num_rows > 0) {
            echo "<script>alert('Ghế $seat_number đã được đặt, vui lòng chọn lại!'); window.location.href = 'movie.php?movie_id=$movie_id';</script>";
            $stmt_check_seat->close();
            continue;
        }

        $total_amount += $ticket_price;
        $seats_to_book[] = ['seat_number' => $seat_number, 'seat_type' => $seat_type, 'ticket_price' => $ticket_price];
        $stmt_check_seat->close();
    }

    // Lưu thông tin vào session để chuyển sang payment.php
    $_SESSION['payment_data'] = [
        'title' => $title,
        'customer_name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name'],
        'email' => $_SESSION['email'],
        'movie_name' => $title,
        'movie_id' => $movie_id,
        'showtime_id' => $showtime_id,
        'show_date' => date('d/m/Y', strtotime($showtime_data['show_date'])),
        'show_time' => date('H:i', strtotime($showtime_data['show_time'])),
        'theater' => 'Rạp Galaxy Nguyễn Trãi',
        'total_amount' => $total_amount,
        'seats' => implode(', ', $seat_numbers),
        'seats_to_book' => $seats_to_book, // Lưu danh sách ghế để xử lý ở payment.php
        'user_id' => $user_id
    ];

    echo "<script>window.location.href = 'payment.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/css/index.css">
    <link rel="stylesheet" href="../Assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSB7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title><?php echo $title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html {
            font-size: 62.5%; 
        }

        body {
            background-color: #575454;
            color: #ffffff;
        }

        .movie-page-container {
            background-color: #000000;
            margin: 40px 137px;
            padding: 20px;
        }

        .movie-page-grid {
            width: 1200px;
            max-width: 100%;
            margin: 0 auto;
        }

        .movie-page-detail {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .movie-page-image {
            flex: 1;
            min-width: 300px;
        }

        .movie-page-image img {
            width: 100%;
            max-width: 300px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .movie-page-info {
            flex: 2;
            min-width: 300px;
        }

        .movie-page-info h1 {
            font-size: 2.4rem;
            margin-bottom: 10px;
            color: #ffffff;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            line-height: 24px;
            max-height: 72px;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .movie-page-info p {
            font-size: 1.8rem;
            margin: 5px 0;
            color: #ffffff;
        }

        .movie-page-description {
            margin: 20px 0;
        }

        .movie-page-description h2 {
            font-size: 1.6rem;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .movie-page-description p {
            font-size: 1.8rem;
            color: #ffffff;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            line-height: 24px;
            max-height: 72px;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* Lịch chiếu và sơ đồ ghế */
        .movie-page-schedule-and-seats {
            margin-top: 20px;
        }

        .movie-page-schedule-and-seats h2 {
            font-size: 1.6rem;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .movie-page-date-buttons, .movie-page-showtime-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .movie-page-btn {
            padding: 6px 12px;
            border: 1px solid #333333;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.6rem;
            background-color: #6c757d;
            color: #ffffff;
            transition: background-color 0.3s;
        }

        .movie-page-btn.active {
            background-color: #007bff;
        }

        .movie-page-btn:hover {
            background-color: #0056b3;
        }

        .movie-page-seat-grid {
            display: grid;
            grid-template-columns: repeat(10, 50px);
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .movie-page-seat {
            width: 50px;
            height: 50px;
            background-color: #f0f0f0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1.4rem;
            color: gray;
        }

        .movie-page-seat.selected {
            background-color: #51cf66;
            color: #ffffff;
        }

        .movie-page-seat.booked {
            background-color: #ff6b6b;
            color: #ffffff;
            cursor: not-allowed;
        }

        .movie-page-seat.vip {
            border: 2px solid #ffc107;
        }

        .movie-page-seat.couple {
            background-color: #ff69b4;
            grid-column: span 2;
            width: 100px;
        }

        .movie-page-seat-legend {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            justify-content: center;
        }

        .movie-page-seat-legend div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .movie-page-seat-legend .color-box {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }

        .movie-page-seat-legend label {
            font-size: 1.6rem;
            color: #ffffff;
        }

        /* Thông tin vé */
        .movie-page-ticket-info {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #333333;
            border-radius: 10px;
            background-color: #1a1a1a;
            transition: box-shadow 0.3s ease;
        }

        .movie-page-ticket-info:hover {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

        .movie-page-ticket-info p {
            font-size: 1.8rem;
            color: #ffffff;
            margin: 5px 0;
            display: flex;
            align-items: center;
        }

        .movie-page-ticket-info p strong {
            font-weight: bold;
            color: #007bff;
        }

        .movie-page-ticket-info p span {
            color: #e0e0e0;
        }

        .movie-page-action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .movie-page-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
        }

        .movie-page-modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 30px;
            width: 60%;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-height: 80vh;
            overflow-y: auto;
        }

        .movie-page-modal-content h2 {
            font-size: 20px;
            color: #333333;
            margin-bottom: 20px;
        }

        .movie-page-modal-content input {
            font-size: 30px;
            color: #333333;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .movie-page-detail {
                flex-direction: column;
                align-items: center;
            }
            .movie-page-image, .movie-page-info {
                flex: none;
                width: 100%;
                text-align: center;
            }
            .movie-page-image img {
                margin: 0 auto;
            }
            .movie-page-ticket-info p {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
<div id="header">
    <div class="grid">
        <div class="navbar">
            <ul>
                <li>
                    <a href="../index.php"> <img class="logo" src="../Assets/images/logo.jpg" alt="" onerror="this.style.display='none';console.log('File logo.jpg not found');"></a>
                </li>
                <li>
                    <a href="../index.php">Trang chủ</a>
                </li>
                <li>
                    <a href="../index.php?act=schedule">Lịch chiếu</a>
                </li>
                <li>
                    <a href="../index.php?act=ticket_price">Giá vé</a>
                </li>
                <li>
                    <a href="../index.php?act=promotions">Khuyến mãi</a>
                </li>
                <li><a href="../index.php?act=about">Giới thiệu</a></li>
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] === 'user') {
                    echo '<li class="resgistion"><a href="../index.php?act=logout">Đăng xuất</a></li>';
                } else {
                    echo '<li class="resgistion"><a href="../index.php?act=register">Đăng ký</a></li>';
                    echo '<li class="log_in"><a href="../index.php?act=login">Đăng nhập</a></li>';
                }
                ?>
                <li>
                    <a href="../index.php?act=search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="content">
    <div id="movie-page-container" class="movie-page-container">
        <div class="movie-page-grid">
            <div class="movie-page-content">
                <!-- Thông tin phim -->
                <div class="movie-page-detail">
                    <div class="movie-page-image">
                        <img src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>" onerror="this.src='../Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo $img_url; ?> not found, fallback to movie2.webp');">
                    </div>
                    <div class="movie-page-info">
                        <h1><?php echo $title; ?></h1>
                        <p>Quốc gia: Việt Nam</p>
                        <p>Thời lượng: <?php echo $duration; ?> Phút</p>
                        <p>Đạo diễn: <?php echo $director; ?></p>
                        <p>Diễn viên: <?php echo $cast; ?></p>
                        <p>Khởi chiếu: <?php echo $formatted_date; ?></p>
                        <p>Kiểm duyệt: Phim phổ biến với mọi độ tuổi</p>
                        <p>Điểm đánh giá: <?php echo $rating; ?>/10</p>
                    </div>
                </div>

                <!-- Mô tả phim -->
                <div class="movie-page-description">
                    <h2>Mô tả</h2>
                    <p><?php echo $description; ?></p>
                </div>

                <!-- Lịch chiếu và sơ đồ ghế -->
                <div class="movie-page-schedule-and-seats">
                    <h2>Lịch chiếu</h2>
                    <div class="movie-page-date-buttons">
                        <?php
                        if ($showtimes_result->num_rows > 0) {
                            $showtimes_result->data_seek(0);
                            $first_date = $showtimes_result->fetch_assoc()['show_date'];
                            $showtimes_result->data_seek(0);
                            $dates = [];
                            while ($showtime = $showtimes_result->fetch_assoc()) {
                                $dates[$showtime['show_date']][] = $showtime;
                            }
                            $first = true;
                            foreach ($dates as $show_date => $times) {
                                $formatted_date = date('d/m/Y', strtotime($show_date));
                                $day_of_week = date('N', strtotime($show_date));
                                $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                                $day_name = $days[$day_of_week - 1];
                                $active = $first ? 'active' : '';
                                echo "<button class='movie-page-btn $active' data-date='$show_date'>$day_name $formatted_date</button>";
                                $first = false;
                            }
                        } else {
                            echo '<p style="font-size: 1.8rem; color: #ffffff;">Hiện tại không có lịch chiếu.</p>';
                        }
                        ?>
                    </div>

                    <div class="movie-page-showtime-buttons">
                        <?php
                        if ($showtimes_result->num_rows > 0) {
                            $showtimes_result->data_seek(0);
                            $first_date = array_key_first($dates);
                            foreach ($dates[$first_date] as $time) {
                                $show_time = date('H:i', strtotime($time['show_time']));
                                $showtime_id = htmlspecialchars($time['showtime_id']);
                                $ticket_price = htmlspecialchars($time['ticket_price']);
                                echo "<button class='movie-page-btn' data-showtime-id='$showtime_id' data-ticket-price='$ticket_price'>$show_time</button>";
                            }
                        }
                        ?>
                    </div>

                    <h2>Sơ đồ ghế</h2>
                    <div>
                        <?php
                        if ($showtimes_result->num_rows > 0) {
                            $first_showtime = $dates[$first_date][0];
                            $showtime_id = $first_showtime['showtime_id'];
                            $stmt_seats = $conn->prepare("SELECT s.seat_id, s.seat_number, s.seat_type, s.is_available 
                                                          FROM seats s 
                                                          JOIN showtimes st ON s.screen_id = st.screen_id 
                                                          WHERE st.showtime_id = ?");
                            $stmt_seats->bind_param("i", $showtime_id);
                            $stmt_seats->execute();
                            $seats_result = $stmt_seats->get_result();

                            echo '<div class="movie-page-seat-grid" id="seatGrid">';
                            while ($seat = $seats_result->fetch_assoc()) {
                                $seat_number = htmlspecialchars($seat['seat_number']);
                                $seat_type = htmlspecialchars($seat['seat_type']);
                                $is_available = $seat['is_available'];
                                $seat_class = $seat_type === 'vip' ? 'vip' : ($seat_type === 'couple' ? 'couple' : '');
                                $booked_class = $is_available ? '' : 'booked';
                                echo "<div class='movie-page-seat $seat_class $booked_class' data-seat-number='$seat_number' data-seat-type='$seat_type'>$seat_number</div>";
                            }
                            echo '</div>';
                            $stmt_seats->close();
                        } else {
                            echo '<p style="font-size: 1.8rem; color: #ffffff;">Vui lòng chọn ngày và giờ chiếu để xem ghế.</p>';
                        }
                        ?>
                    </div>

                    <div class="movie-page-seat-legend">
                        <div><div class="color-box" style="background-color: #f0f0f0;"></div> <label>Ghế thường</label></div>
                        <div><div class="color-box" style="border: 2px solid #ffc107;"></div> <label>Ghế VIP</label></div>
                        <div><div class="color-box" style="background-color: #ff69b4;"></div> <label>Ghế đôi</label></div>
                        <div><div class="color-box" style="background-color: #ff6b6b;"></div> <label>Ghế đã đặt</label></div>
                    </div>
                </div>

                <!-- Thông tin vé và nút thanh toán -->
                <div class="movie-page-ticket-info">
                    <p><strong>Ghế đã chọn:</strong> <span id="selectedSeats">Chưa chọn</span></p>
                    <p><strong>Tổng tiền:</strong> <span id="totalPrice">0</span>đ</p>
                </div>

                <div class="movie-page-action-buttons">
                    <button class="movie-page-btn" onclick="window.history.back()">Quay lại</button>
                    <button class="movie-page-btn" onclick="openPaymentModal()">Thanh toán</button>
                </div>

                <!-- Modal thanh toán -->
                <div id="paymentModal" class="movie-page-modal">
                    <div class="movie-page-modal-content">
                        <h2>Thông tin vé</h2>
                        <div class="movie-page-ticket-info" id="ticketInfo"></div>
                        <form method="POST" id="paymentForm" action="">
                            <input type="hidden" name="book_ticket" value="1">
                            <input type="hidden" name="movie_id" id="payment_movie_id">
                            <input type="hidden" name="showtime_id" id="payment_showtime_id">
                            <input type="hidden" name="seat_number" id="payment_seat_number" value="">
                            <input type="hidden" name="seat_type" id="payment_seat_type" value="">
                            <input type="hidden" name="ticket_price" id="payment_ticket_price">
                            <button type="submit" class="movie-page-btn">Xác nhận thanh toán</button>
                            <button type="button" class="movie-page-btn" onclick="closePaymentModal()">Đóng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../footer.php"; ?>

    <script>
        let selectedSeats = [];
        let selectedShowtimeId = "<?php echo $showtimes_result->num_rows > 0 ? $first_showtime['showtime_id'] : 0; ?>";
        let selectedTicketPrice = "<?php echo $showtimes_result->num_rows > 0 ? $first_showtime['ticket_price'] : 0; ?>";

        // Chọn ngày
        document.querySelectorAll('.movie-page-date-buttons .movie-page-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.movie-page-date-buttons .movie-page-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const date = button.getAttribute('data-date');
                fetchShowtimes(date);
            });
        });

        // Lấy danh sách suất chiếu theo ngày
        function fetchShowtimes(date) {
            const showtimes = <?php
                $showtimes_json = [];
                foreach ($dates as $show_date => $times) {
                    $times_json = [];
                    foreach ($times as $time) {
                        $times_json[] = [
                            'showtime_id' => $time['showtime_id'],
                            'show_time' => date('H:i', strtotime($time['show_time'])),
                            'ticket_price' => $time['ticket_price'],
                            'screen_id' => $time['screen_id']
                        ];
                    }
                    $showtimes_json[$show_date] = $times_json;
                }
                echo json_encode($showtimes_json);
            ?>;
            
            const times = showtimes[date];
            const showtimeContainer = document.querySelector('.movie-page-showtime-buttons');
            showtimeContainer.innerHTML = '';
            times.forEach(time => {
                const button = document.createElement('button');
                button.classList.add('movie-page-btn');
                button.setAttribute('data-showtime-id', time.showtime_id);
                button.setAttribute('data-ticket-price', time.ticket_price);
                button.textContent = time.show_time;
                button.addEventListener('click', () => selectShowtime(time.showtime_id, time.ticket_price, time.screen_id));
                showtimeContainer.appendChild(button);
            });

            // Chọn suất chiếu đầu tiên
            if (times.length > 0) {
                selectShowtime(times[0].showtime_id, times[0].ticket_price, times[0].screen_id);
            }
        }

        // Chọn suất chiếu
        function selectShowtime(showtimeId, ticketPrice, screenId) {
            selectedShowtimeId = showtimeId;
            selectedTicketPrice = ticketPrice;
            document.querySelectorAll('.movie-page-showtime-buttons .movie-page-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`.movie-page-showtime-buttons .movie-page-btn[data-showtime-id="${showtimeId}"]`).classList.add('active');
            fetchSeats(showtimeId, screenId);
        }

        // Lấy danh sách ghế
        function fetchSeats(showtimeId, screenId) {
            const seatGrid = document.getElementById('seatGrid');
            seatGrid.innerHTML = '';
            fetch(`../catalog/get_booked_seats.php?showtime_id=${showtimeId}`)
                .then(response => response.json())
                .then(bookedSeats => {
                    <?php
                    $stmt_seats = $conn->prepare("SELECT s.seat_id, s.seat_number, s.seat_type, s.is_available 
                                                  FROM seats s 
                                                  WHERE s.screen_id = ?");
                    $stmt_seats->bind_param("i", $first_showtime['screen_id']);
                    $stmt_seats->execute();
                    $seats_result = $stmt_seats->get_result();
                    $seats_json = [];
                    while ($seat = $seats_result->fetch_assoc()) {
                        $seats_json[] = [
                            'seat_number' => $seat['seat_number'],
                            'seat_type' => $seat['seat_type'],
                            'is_available' => $seat['is_available']
                        ];
                    }
                    $stmt_seats->close();
                    echo "const allSeats = " . json_encode($seats_json) . ";";
                    ?>
                    allSeats.forEach(seat => {
                        const seatElement = document.createElement('div');
                        seatElement.classList.add('movie-page-seat');
                        const seatType = seat.seat_type;
                        if (seatType === 'vip') seatElement.classList.add('vip');
                        if (seatType === 'couple') seatElement.classList.add('couple');
                        if (!seat.is_available || bookedSeats.includes(seat.seat_number)) {
                            seatElement.classList.add('booked');
                        }
                        seatElement.setAttribute('data-seat-number', seat.seat_number);
                        seatElement.setAttribute('data-seat-type', seatType);
                        seatElement.textContent = seat.seat_number;
                        seatElement.addEventListener('click', () => selectSeat(seatElement, seat.seat_number, seatType));
                        seatGrid.appendChild(seatElement);
                    });
                });
        }

        // Chọn ghế
        function selectSeat(seatElement, seatNumber, seatType) {
            if (seatElement.classList.contains('booked')) return;

            const index = selectedSeats.findIndex(s => s.number === seatNumber);
            if (index === -1) {
                if (seatType === 'couple' && selectedSeats.some(s => s.type === 'couple')) {
                    alert('Chỉ được chọn 1 ghế đôi cùng lúc!');
                    return;
                }
                seatElement.classList.add('selected');
                selectedSeats.push({ number: seatNumber, type: seatType });
            } else {
                seatElement.classList.remove('selected');
                selectedSeats.splice(index, 1);
            }
            updateSelectedSeatsDisplay();
            updateTotalPrice();
        }

        // Cập nhật hiển thị ghế đã chọn
        function updateSelectedSeatsDisplay() {
            const selectedSeatsElement = document.getElementById('selectedSeats');
            if (selectedSeats.length === 0) {
                selectedSeatsElement.textContent = 'Chưa chọn';
            } else {
                selectedSeatsElement.textContent = selectedSeats.map(s => `${s.number} (${s.type})`).join(', ');
            }
        }

        // Cập nhật tổng tiền
        function updateTotalPrice() {
            const totalPriceElement = document.getElementById('totalPrice');
            let total = 0;
            selectedSeats.forEach(seat => {
                if (seat.type === 'standard') total += 60000;
                else if (seat.type === 'vip') total += 80000;
                else if (seat.type === 'couple') total += 150000;
            });
            totalPriceElement.textContent = total;
        }

        // Mở modal thanh toán
        function openPaymentModal() {
            if (selectedSeats.length === 0) {
                alert('Vui lòng chọn ít nhất 1 ghế!');
                return;
            }
            if (!selectedShowtimeId) {
                alert('Vui lòng chọn suất chiếu!');
                return;
            }

            document.getElementById('payment_movie_id').value = <?php echo $movie_id; ?>;
            document.getElementById('payment_showtime_id').value = selectedShowtimeId;
            const seatNumbersInput = document.getElementById('payment_seat_number');
            const seatTypesInput = document.getElementById('payment_seat_type');
            seatNumbersInput.value = JSON.stringify(selectedSeats.map(s => s.number));
            seatTypesInput.value = JSON.stringify(selectedSeats.map(s => s.type));
            const totalPrice = selectedSeats.reduce((sum, seat) => {
                return sum + (seat.type === 'standard' ? 60000 : seat.type === 'vip' ? 80000 : 150000);
            }, 0);
            document.getElementById('payment_ticket_price').value = totalPrice;

            const ticketInfo = document.getElementById('ticketInfo');
            const activeDate = document.querySelector('.movie-page-date-buttons .movie-page-btn.active')?.textContent || '';
            const activeTime = document.querySelector('.movie-page-showtime-buttons .movie-page-btn.active')?.textContent || '';
            ticketInfo.innerHTML = `
                <p><strong>Phim:</strong> <?php echo $title; ?></p>
                <p><strong>Ngày chiếu:</strong> ${activeDate}</p>
                <p><strong>Giờ chiếu:</strong> ${activeTime}</p>
                <p><strong>Ghế:</strong> ${selectedSeats.map(s => `${s.number} (${s.type})`).join(', ')}</p>
                <p><strong>Giá vé:</strong> ${totalPrice} VNĐ</p>
            `;

            document.getElementById('paymentModal').style.display = 'block';
        }

        // Đóng modal
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        // Đóng modal khi nhấp bên ngoài
        window.onclick = function(event) {
            if (event.target.classList.contains('movie-page-modal')) {
                closePaymentModal();
            }
        };
    </script>
</body>
</html>
<?php
$stmt_dates->close();
$conn->close();
?>
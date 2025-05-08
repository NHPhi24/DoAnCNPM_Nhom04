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
    <link rel="stylesheet" href="../Assets/css/login.css">
    <link rel="stylesheet" href="../Assets/css/resgistion.css">
    <link rel="stylesheet" href="../Assets/css/movie.css">
    <title>Movies</title>
    <style>
        .selected-seats-list {
            margin-top: 10px;
            max-height: 100px;
            overflow-y: auto;
            border: 1px solid #444;
            border-radius: 5px;
            padding: 5px;
        }
        .selected-seat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            background-color: #333;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        .selected-seat-item span {
            color: #fff;
        }
        .selected-seat-item .remove-seat {
            cursor: pointer;
            color: #ff5555;
            font-size: 14px;
        }
        .selected-seat-item .remove-seat:hover {
            color: #ff0000;
        }
    </style>
</head>

<body>
    <div id="content">
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
        <div id="container">
            <div class="grid">
                <div class="container">
                    <div>
                        <?php
                        require_once '../backend/connect.php';

                        $movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

                        if ($movie_id <= 0) {
                            echo '<p style="color: red; text-align: center;">Phim không tồn tại.</p>';
                            exit();
                        }

                        $stmt = $conn->prepare("SELECT title, genre, duration, director, cast, description, release_date, rating, img_url FROM Movies WHERE movie_id = ?");
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
                        ?>
                        <div class="inf-movies">
                            <img src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>" onerror="this.src='../Assets/images/movie2.webp';this.onerror=null;console.log('File <?php echo $img_url; ?> not found, fallback to movie2.webp');">
                            <div class="information-movie">
                                <h1 class="text-warp"><?php echo $title; ?></h1>
                                <div class="content">
                                    <p class="category"><?php echo $genre; ?></p>
                                    <p class="country">Hàn Quốc</p>
                                    <p class="time"><?php echo $duration; ?> Phút</p>
                                    <p class="director">Đạo diễn: <?php echo $director; ?></p>
                                </div>
                                <p class="actor">Diễn viên: <?php echo $cast; ?></p>
                                <p class="start">Khởi chiếu: <?php echo $formatted_date; ?></p>
                                <span class="text-warp"><?php echo $description; ?></span>
                                <p>Kiểm duyệt: Điểm đánh giá: <?php echo $rating; ?>/10</p>
                            </div>
                        </div>
                        <div class="saperate"></div>
                        <div class="chose-day">
                            <?php
                            $stmt = $conn->prepare("SELECT showtime_id, show_date FROM Showtimes WHERE movie_id = ? AND show_date >= CURDATE() GROUP BY show_date ORDER BY show_date");
                            $stmt->bind_param("i", $movie_id);
                            $stmt->execute();
                            $showtimes_result = $stmt->get_result();

                            $first = true;
                            while ($showtime = $showtimes_result->fetch_assoc()) {
                                $show_date = htmlspecialchars($showtime['show_date']);
                                $formatted_date = date('d/m/Y', strtotime($show_date));
                                $day_of_week = date('N', strtotime($show_date));
                                $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                                $day_name = $days[$day_of_week];
                                $day_number = date('d', strtotime($show_date));
                                $month = 'Th.' . date('m', strtotime($show_date));
                                ?>
                                <div class="day-in-week <?php echo $first ? 'active' : ''; ?>" data-date="<?php echo $show_date; ?>">
                                    <p><?php echo $day_name; ?></p>
                                    <h1><?php echo $day_number; ?></h1>
                                    <p><?php echo $month; ?></p>
                                </div>
                                <?php
                                $first = false;
                            }
                            ?>
                        </div>
                        <div class="saperate"></div>
                        <div class="reservation">
                            <div class="set-time">
                                <div class="schedule-time" id="schedule-time">
                                    <?php
                                    // Kiểm tra xem có lịch chiếu nào không
                                    if ($showtimes_result->num_rows > 0) {
                                        $showtimes_result->data_seek(0);
                                        $first_date = $showtimes_result->fetch_assoc()['show_date'];
                                        $stmt = $conn->prepare("SELECT showtime_id, show_time, ticket_price FROM Showtimes WHERE movie_id = ? AND show_date = ? ORDER BY show_time");
                                        $stmt->bind_param("is", $movie_id, $first_date);
                                        $stmt->execute();
                                        $times_result = $stmt->get_result();

                                        if ($times_result->num_rows > 0) {
                                            while ($time = $times_result->fetch_assoc()) {
                                                $show_time = htmlspecialchars($time['show_time']);
                                                $formatted_time = date('H:i', strtotime($show_time));
                                                $showtime_id = htmlspecialchars($time['showtime_id']);
                                                $ticket_price = htmlspecialchars($time['ticket_price']);
                                                ?>
                                                <p class="time-slot" data-showtime-id="<?php echo $showtime_id; ?>" data-price="<?php echo $ticket_price; ?>"><?php echo $formatted_time; ?></p>
                                                <?php
                                            }
                                        } else {
                                            echo '<p style="color: #aaa; text-align: center;">Không có suất chiếu nào cho ngày này.</p>';
                                        }
                                    } else {
                                        echo '<p style="color: #aaa; text-align: center;">Hiện tại không có lịch chiếu.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="seat" id="seat-selection">
                                <?php
                                if (isset($times_result) && $times_result->num_rows > 0) {
                                    $times_result->data_seek(0);
                                    $first_showtime = $times_result->fetch_assoc();
                                    $showtime_id = $first_showtime ? $first_showtime['showtime_id'] : 0;

                                    if ($showtime_id) {
                                        $stmt = $conn->prepare("SELECT s.seat_id, s.seat_number, s.seat_type, s.is_available 
                                                                FROM Seats s 
                                                                JOIN Showtimes st ON s.screen_id = st.screen_id 
                                                                WHERE st.showtime_id = ?");
                                        $stmt->bind_param("i", $showtime_id);
                                        $stmt->execute();
                                        $seats_result = $stmt->get_result();

                                        while ($seat = $seats_result->fetch_assoc()) {
                                            $seat_number = htmlspecialchars($seat['seat_number']);
                                            $seat_type = htmlspecialchars($seat['seat_type']);
                                            $is_available = $seat['is_available'];
                                            $seat_class = $seat_type === 'vip' ? 'vip' : ($seat_number[0] === 'H' ? 'seat-couple' : '');
                                            $disabled = $is_available ? '' : 'disabled';
                                            ?>
                                            <button class="btn btn-seat <?php echo $seat_class; ?>" data-seat-id="<?php echo $seat['seat_id']; ?>" data-seat-number="<?php echo $seat_number; ?>" data-seat-type="<?php echo $seat_type; ?>" <?php echo $disabled; ?>>
                                                <?php echo $seat_number; ?>
                                            </button>
                                            <?php
                                        }
                                    } else {
                                        echo '<p style="color: #aaa; text-align: center;">Không có ghế nào để hiển thị.</p>';
                                    }
                                } else {
                                    echo '<p style="color: #aaa; text-align: center;">Vui lòng chọn ngày và giờ chiếu để xem ghế.</p>';
                                }
                                ?>
                            </div>
                            <div class="note">
                                <div class="seat-note">
                                    <div class="color-seat" style="background-color: #f9f9f9;">.</div>
                                    <label for="">Ghế thường</label>
                                </div>
                                <div class="seat-note">
                                    <div class="color-seat" style="background-color: #ffc107;">.</div>
                                    <label for="">Ghế vip</label>
                                </div>
                                <div class="seat-note">
                                    <div class="color-seat" style="background-color: #e91e63;"></div>
                                    <label for="">Ghế đôi</label>
                                </div>
                            </div>
                        </div>
                        <div class="saperate"></div>
                        <div class="payment">
                            <div class="seat-chose">
                                <div class="seat-chosed">
                                    <label for="">Ghế đã chọn: </label>
                                    <div class="selected-seats-list" id="selected-seats-list"></div>
                                </div>
                                <div class="total">
                                    <label for="">Tổng tiền: </label>
                                    <p id="total-price">0đ</p>
                                </div>
                            </div>
                            <div class="pay">
                                <button class="btn" onclick="history.back()">Quay lại</button>
                                <button class="btn btn-pay" id="pay-button" disabled>Thanh toán</button>
                            </div>
                        </div>
                        <?php
                        } else {
                            echo '<p style="color: red; text-align: center;">Phim không tồn tại.</p>';
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="footer">
        <div class="grid">
            <div class="footer-container">
                <div class="footer">
                    <ul>
                        <li><a href="">Chính sách</a></li>
                        <li><a href="">Lịch chiếu</a></li>
                        <li><a href="">Tin tức</a></li>
                        <li><a href="">Giá vé</a></li>
                        <li><a href="">Hỏi đáp</a></li>
                        <li><a href="">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer_icon">
                    <ul>
                        <li><i class="fa-brands fa-facebook"></i></li>
                        <li><i class="fa-brands fa-youtube"></i></li>
                        <li><i class="fa-brands fa-instagram"></i></li>
                        <li><i class="fa-brands fa-google-play"></i></li>
                        <li><i class="fa-brands fa-app-store-ios"></i></li>
                    </ul>
                </div>
                <div class="footer_inf">
                    <p>Cơ quan chủ quản: .... , .... <br>
                        Bản quyền thuộc Trung tâm Chiếu phim quốc gia <br>
                        Giấy phép số ..../GP-TTĐT ngày .../.../... - Chịu trách nhiệm: nhóm 04 <br>
                        Địa chỉ: Đại học Mỏ Địa chất
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/JS/reservation.js"></script>
    <script>
        const days = document.querySelectorAll('#chose-day .day-in-week');
        const scheduleTime = document.getElementById('schedule-time');
        const seatSelection = document.getElementById('seat-selection');
        const selectedSeatsList = document.getElementById('selected-seats-list');
        const totalPriceDisplay = document.getElementById('total-price');
        const payButton = document.getElementById('pay-button');

        let selectedSeats = [];
        let selectedShowtimeId = null;
        let ticketPrice = 0;

        days.forEach(day => {
            day.addEventListener('click', async () => {
                days.forEach(d => d.classList.remove('active'));
                day.classList.add('active');

                const showDate = day.dataset.date;
                const movieId = <?php echo $movie_id; ?>;

                const response = await fetch(`get_showtimes.php?movie_id=${movieId}&show_date=${showDate}`);
                const times = await response.json();

                scheduleTime.innerHTML = '';
                if (times.length > 0) {
                    times.forEach(time => {
                        const p = document.createElement('p');
                        p.classList.add('time-slot');
                        p.dataset.showtimeId = time.showtime_id;
                        p.dataset.price = time.ticket_price;
                        p.textContent = new Date(`1970-01-01T${time.show_time}Z`).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                        scheduleTime.appendChild(p);
                    });
                } else {
                    scheduleTime.innerHTML = '<p style="color: #aaa; text-align: center;">Không có suất chiếu nào cho ngày này.</p>';
                }

                selectedSeats = [];
                updateSelection();
                addTimeSlotEvents();
            });
        });

        function addTimeSlotEvents() {
            const timeSlots = scheduleTime.querySelectorAll('.time-slot');
            timeSlots.forEach(slot => {
                slot.addEventListener('click', async () => {
                    timeSlots.forEach(s => s.classList.remove('active'));
                    slot.classList.add('active');

                    selectedShowtimeId = slot.dataset.showtimeId;
                    ticketPrice = parseFloat(slot.dataset.price);

                    const response = await fetch(`get_seats.php?showtime_id=${selectedShowtimeId}`);
                    const seats = await response.json();

                    seatSelection.innerHTML = '';
                    if (seats.length > 0) {
                        seats.forEach(seat => {
                            const button = document.createElement('button');
                            button.classList.add('btn', 'btn-seat');
                            if (seat.seat_type === 'vip') button.classList.add('vip');
                            if (seat.seat_number.startsWith('H')) button.classList.add('seat-couple');
                            if (!seat.is_available) button.disabled = true;
                            button.dataset.seatId = seat.seat_id;
                            button.dataset.seatNumber = seat.seat_number;
                            button.dataset.seatType = seat.seat_type;
                            button.textContent = seat.seat_number;
                            if (selectedSeats.some(s => s.seatId === seat.seat_id)) {
                                button.classList.add('selected');
                            }
                            seatSelection.appendChild(button);
                        });
                    } else {
                        seatSelection.innerHTML = '<p style="color: #aaa; text-align: center;">Không có ghế nào để hiển thị.</p>';
                    }

                    updateSelection();
                    addSeatEvents();
                });
            });
        }

        function addSeatEvents() {
            const seats = seatSelection.querySelectorAll('.btn-seat');
            seats.forEach(seat => {
                seat.addEventListener('click', () => {
                    if (seat.disabled) return;

                    const seatNumber = seat.dataset.seatNumber;
                    const seatId = seat.dataset.seatId;
                    const seatType = seat.dataset.seatType;

                    if (selectedSeats.some(s => s.seatId === seatId)) {
                        selectedSeats = selectedSeats.filter(s => s.seatId !== seatId);
                        seat.classList.remove('selected');
                    } else {
                        selectedSeats.push({ seatId, seatNumber, seatType });
                        seat.classList.add('selected');
                    }

                    updateSelection();
                });
            });
        }

        function updateSelection() {
            selectedSeatsList.innerHTML = '';
            if (selectedSeats.length === 0) {
                selectedSeatsList.innerHTML = '<p style="color: #aaa; margin: 5px;">Chưa chọn ghế</p>';
            } else {
                selectedSeats.forEach(seat => {
                    const div = document.createElement('div');
                    div.classList.add('selected-seat-item');
                    const typeLabel = seat.seatType === 'vip' ? 'VIP' : (seat.seatNumber.startsWith('H') ? 'Ghế đôi' : 'Thường');
                    div.innerHTML = `<span>${seat.seatNumber} (${typeLabel})</span><span class="remove-seat" data-seat-id="${seat.seatId}">[Xóa]</span>`;
                    selectedSeatsList.appendChild(div);
                });

                const removeButtons = selectedSeatsList.querySelectorAll('.remove-seat');
                removeButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const seatId = button.dataset.seatId;
                        selectedSeats = selectedSeats.filter(s => s.seatId !== seatId);
                        const seatButton = seatSelection.querySelector(`.btn-seat[data-seat-id="${seatId}"]`);
                        if (seatButton) seatButton.classList.remove('selected');
                        updateSelection();
                    });
                });
            }

            updateTotalPrice();
        }

        function updateTotalPrice() {
            const total = selectedSeats.length * ticketPrice;
            totalPriceDisplay.textContent = total.toLocaleString('vi-VN') + 'đ';
            payButton.disabled = selectedSeats.length === 0;
        }

        payButton.addEventListener('click', async () => {
            if (!selectedShowtimeId) {
                alert('Vui lòng chọn giờ chiếu.');
                return;
            }
            if (selectedSeats.length === 0) {
                alert('Vui lòng chọn ít nhất một ghế ngồi.');
                return;
            }

            const bookingData = {
                user_id: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>,
                showtime_id: selectedShowtimeId,
                seat_ids: selectedSeats.map(s => s.seatId)
            };

            if (!bookingData.user_id) {
                alert('Vui lòng đăng nhập để đặt vé.');
                window.location.href = '../index.php?act=login';
                return;
            }

            const response = await fetch('book_tickets.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingData)
            });

            const result = await response.json();
            if (!result.success) {
                alert('Đặt vé thất bại: ' + result.message);
                return;
            }

            alert('Đặt vé thành công! Bạn sẽ được chuyển hướng đến trang thanh toán.');
            window.location.href = `payment.php?booking_id=${bookingData.showtime_id}`;
        });

        addTimeSlotEvents();
    </script>
</body>

</html>
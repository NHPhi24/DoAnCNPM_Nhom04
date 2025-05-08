<div id="container">
    <div class="grid">
        <div class="container">
            <div class="container-left">
                <div class="title">
                    <p class="title-left live_movie ">Phim đang chiếu</p>
                    <p class="title-right"> <a href="">Xem tất cả</a></p>
                </div>
                <div class="content box-warp">
                    <?php
                    // Kết nối cơ sở dữ liệu
                    require_once './backend/connect.php';

                    // Truy vấn lấy danh sách phim từ bảng Movies
                    $sql = "SELECT movie_id, title, genre, release_date, img_url FROM Movies WHERE release_date <= CURDATE() ORDER BY release_date DESC";
                    $result = $conn->query($sql);

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
                        echo '<p style="color: #aaa; text-align: center; width: 100%;">Hiện tại không có phim đang chiếu.</p>';
                    }

                    // Đóng kết nối
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="container-right">
                <div class="title">
                    <p class="title-left ">Khuyến mãi</p>
                    <p class="title-right"> <a href="">Xem tất cả</a></p>
                </div>
                <div class="promotion">
                    <a href=""><img src="./Assets/images/Movie1.webp" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
                    <a href=""><img src="./Assets/images/Movie1.webp" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
                    <a href=""><img src="./Assets/images/Movie1.webp" alt="" onerror="this.style.display='none';console.log('File Movie1.webp not found');"></a>
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
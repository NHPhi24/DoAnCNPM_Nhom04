<?php

$connect_path = './backend/connect.php';
if (!file_exists($connect_path)) {
    die("Lỗi: File connect.php không tồn tại tại đường dẫn $connect_path. Vui lòng kiểm tra lại.");
}
require_once $connect_path;

// Lấy danh sách khuyến mãi
$current_date = '2025-05-26';
$sql = "SELECT title, description, start_date, end_date, image_url 
        FROM promotions 
        WHERE end_date >= ? 
        ORDER BY start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
$filtered_promotions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
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
    <title>Khuyến Mãi</title>
    <style>
        .promotions-container {
            max-width: 1200px;
            margin: 50px auto;
            background-color: #fff;
            color: #000;
            padding: 65px;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            color: #ff1493;
            margin-bottom: 20px;
            font-size: 45px;
        }
        .promotions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .promotion-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        .promotion-card:hover {
            transform: translateY(-5px);
        }
        .promotion-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .promotion-card h3 {
            font-size: 18px;
            color: #ff1493;
            margin-bottom: 10px;
        }
        .promotion-card p {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
        }
        .promotion-card .date {
            font-size: 12px;
            color: #777;
        }
        .no-promotions {
            text-align: center;
            font-size: 16px;
            color: #333;
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="promotions-container">
        <h1>KHUYẾN MÃI</h1>
        <div class="promotions-list">
            <?php if (!empty($filtered_promotions)): ?>
                <?php foreach ($filtered_promotions as $promotion): ?>
                    <div class="promotion-card">
                        <?php if (!empty($promotion['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($promotion['image_url']); ?>" alt="<?php echo htmlspecialchars($promotion['title']); ?>" onerror="this.src='../Assets/images/default_promotion.jpg';this.onerror=null;console.log('File <?php echo htmlspecialchars($promotion['image_url']); ?> not found, fallback to default_promotion.jpg');">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($promotion['title']); ?></h3>
                        <p><?php echo htmlspecialchars($promotion['description']); ?></p>
                        <p class="date">Thời gian áp dụng: <?php echo date('d/m/Y', strtotime($promotion['start_date'])) . ' - ' . date('d/m/Y', strtotime($promotion['end_date'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-promotions">
                    Hiện tại không có chương trình khuyến mãi nào.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "./footer.php"; ?>
</body>
</html>
<?php
header('Content-Type: application/json');
$connect_path = '../backend/connect.php';
if (!file_exists($connect_path)) {
    die(json_encode(['error' => 'File connect.php không tồn tại']));
}
require_once $connect_path;

$showtime_id = isset($_GET['showtime_id']) ? (int)$_GET['showtime_id'] : 0;

$stmt = $conn->prepare("SELECT seat_number FROM Tickets WHERE showtime_id = ? AND status = 'completed'");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_seats = [];
while ($row = $result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}

echo json_encode($booked_seats);

$stmt->close();
$conn->close();
?>
<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$id = $_GET['id'];

// Fetch booking
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id); // bind the $id parameter
$stmt->execute();
$result = $stmt->get_result();      // get the result set
$booking = $result->fetch_assoc();  // fetch one row as associative array
$stmt->close();

// Fetch users
$result = $conn->query("SELECT id, username FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);

// Fetch services
$result = $conn->query("SELECT id, name AS service_name FROM services");
$services = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE bookings SET user_id=?, service_id=?, booking_datetime=?, status=? WHERE id=?");
    $stmt->execute([$user_id, $service_id, $booking_date, $status, $id]);

    header("Location: bookings.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

<h2>Edit Booking</h2>

<form method="post">
    <label>User:</label>
    <select name="user_id" class="form-control mb-3" required>
        <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id']==$booking['user_id']?'selected':'' ?>>
                <?= htmlspecialchars($u['username']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Service:</label>
    <select name="service_id" class="form-control mb-3" required>
        <?php foreach ($services as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $s['id']==$booking['service_id']?'selected':'' ?>>
                <?= htmlspecialchars($s['service_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Date:</label>
    <input type="datetime-local" name="booking_date" value="<?= $booking['booking_datetime'] ?>" class="form-control mb-3" required>

    <label>Status:</label>
    <select name="status" class="form-control mb-3">
        <option <?= $booking['status']=='Pending'?'selected':'' ?>>Pending</option>
        <option <?= $booking['status']=='Confirmed'?'selected':'' ?>>Confirmed</option>
        <option <?= $booking['status']=='Completed'?'selected':'' ?>>Completed</option>
        <option <?= $booking['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
    </select>

    <button class="btn btn-warning">Update</button>
    <a href="bookings.php" class="btn btn-secondary">Cancel</a>
</form>

</body>
</html>
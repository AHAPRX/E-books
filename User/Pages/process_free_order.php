<?php
session_start();
include "../../Config/db.php";

if (empty($_SESSION['cart']) || !isset($_SESSION['user_id'])) {
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
$has_non_pdf = false;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
    if (strtolower($item['format']) !== 'pdf') {
        $has_non_pdf = true;
    }
}

$total = $subtotal + ($has_non_pdf ? ($subtotal >= 50 ? 0 : 5.99) : 0);

if ($total > 0) {
    header("Location: payment.php");
    exit();
}

$conn->begin_transaction();

try {
    foreach ($_SESSION['cart'] as $item) {
        $book_id = $item['id'];
        $qty = $item['qty'];
        $format = ucfirst($item['format']);
        $deliveryAddress = "N/A (PDF)";
        $shipping = 0;
        $amount = 0;

        $sql = "INSERT INTO orders (UserID, BookID, Format, Quantity, ShippingCharge, TotalAmount, DeliveryAddress, PaymentStatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Paid')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisidss", $_SESSION['user_id'], $book_id, $format, $qty, $shipping, $amount, $deliveryAddress);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $_SESSION['cart'] = [];

    header("Location: orderhistory.php?success=1");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Error processing free order: " . $e->getMessage());
}
?>

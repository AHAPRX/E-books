<?php
session_start();
include "../../Config/db.php";

if (!isset($_GET['order_id']) || !isset($_GET['token'])) {
    die("Invalid confirmation link.");
}

$order_id = intval($_GET['order_id']);
$token = $_GET['token'];

// Validate the order and token
$sql = "SELECT UserID, confirmation, confirmation_token FROM orders WHERE OrderID = ? AND confirmation = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order || $order['confirmation_token'] !== $token || $order['UserID'] !== $_SESSION['user_id']) {
    die("Invalid or expired confirmation link.");
}

// Update the confirmation status
$sql = "UPDATE orders SET confirmation = 1, confirmation_token = NULL WHERE OrderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - E - Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {950: '#020617', 900: '#0f172a', 800: '#1e293b', 700: '#334155', 600: '#475569', 500: '#64748b', 400: '#94a3b8', 300: '#cbd5e1'},
                        emerald: {400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857'}
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .glass-effect { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .gradient-bg { background: linear-gradient(135deg, #020617 0%, #1e293b 100%); }
        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center gradient-bg">
    <div class="max-w-md w-full mx-auto px-4 py-8">
        <div class="glass-effect rounded-2xl p-8 animate-fade-in text-center">
            <h1 class="text-3xl font-bold text-white mb-4"><i class="fas fa-check-circle mr-2 text-emerald-400"></i>Order Confirmed!</h1>
            <p class="text-slate-300 mb-6">Your order (ID: <?php echo $order_id; ?>) has been successfully confirmed. Thank you for shopping with E - Books!</p>
            <div class="flex flex-col space-y-4">
                <a href="books.php" class="bg-emerald-500 hover:bg-emerald-600 text-white py-3 px-6 rounded-lg font-semibold transition-all duration-300">Continue Shopping</a>
                <a href="orderhistory.php" class="bg-slate-800/30 text-slate-300 hover:bg-slate-700/50 hover:text-white py-3 px-6 rounded-lg font-semibold transition-all duration-300">View Order Details</a>
            </div>
        </div>
    </div>
</body>
</html>
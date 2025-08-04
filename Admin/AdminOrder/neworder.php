<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'];
    $bookID = $_POST['bookID'];
    $format = $_POST['format'];
    $quantity = $_POST['quantity'];
    $shippingCharge = $_POST['shippingCharge'];
    $totalAmount = $_POST['totalAmount'];
    $orderDate = $_POST['orderDate'];
    $paymentStatus = $_POST['paymentStatus'];
    $deliveryAddress = $_POST['deliveryAddress'];

    $sql = "INSERT INTO orders (UserID, BookID, Format, Quantity, ShippingCharge, TotalAmount, OrderDate, PaymentStatus, DeliveryAddress)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisiddsss", $userID, $bookID, $format, $quantity, $shippingCharge, $totalAmount, $orderDate, $paymentStatus, $deliveryAddress);

    if ($stmt->execute()) {
        header("Location: admin.orders.php");
        exit();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order - E - Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#020617',
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                            600: '#475569',
                            500: '#64748b',
                            400: '#94a3b8',
                            300: '#cbd5e1'
                        },
                        emerald: {
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        #sidebar.collapsed ~ #main-content {
            margin-left: 4rem; /* Matches collapsed sidebar width (w-16) */
        }
        #sidebar:not(.collapsed) ~ #main-content {
            margin-left: 16rem; /* Matches expanded sidebar width (w-64) */
        }
        .scroll-animate {
            opacity: 0;
            transform: translateY(20px);
            transition: all 1s ease-out;
        }
        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .parallax-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        .bg-blur {
            filter: blur(3rem);
        }
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.3s ease;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .mobile-menu.open {
            max-height: 500px;
        }
    </style>
</head>
<body class="bg-slate-950 text-white">
    <!-- Include Navigation -->
    <!-- Include Sidebar -->
    <?php include "../Components/sider.php"; ?>

    <!-- Main Content -->
    <div id="main-content" class="ml-64 transition-all duration-300">
        <section class="py-24 px-6 relative">
            <div class="container mx-auto max-w-2xl">
                <div id="form-container" class="backdrop-blur-2xl bg-slate-800/30 border border-slate-700/50 shadow-2xl hover:shadow-emerald-500/20 transition-all duration-500 group scroll-animate rounded-2xl">
                    <div class="absolute inset-0 bg-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                    <div class="p-10 relative">
                        <h1 class="text-4xl font-black mb-8 text-white group-hover:text-emerald-400 transition-colors duration-300">
                            Add New <span class="text-emerald-400">Order</span>
                        </h1>

                        <form method="POST" class="grid grid-cols-1 gap-6">
                            <!-- User Dropdown -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">User</label>
                                <select name="userID" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300">
                                    <option value="" class="bg-slate-900">Select User</option>
                                    <?php
                                    $userRes = $conn->query("SELECT UserID, Username FROM users");
                                    while ($user = $userRes->fetch_assoc()) {
                                        echo "<option value='" . $user['UserID'] . "' class='bg-slate-900'>" . htmlspecialchars($user['Username']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Book Dropdown -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Book</label>
                                <select name="bookID" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300">
                                    <option value="" class="bg-slate-900">Select Book</option>
                                    <?php
                                    $bookRes = $conn->query("SELECT BookID, Title FROM books");
                                    while ($book = $bookRes->fetch_assoc()) {
                                        echo "<option value='" . $book['BookID'] . "' class='bg-slate-900'>" . htmlspecialchars($book['Title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Format -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Format</label>
                                <select name="format" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300">
                                    <option value="" class="bg-slate-900">Select Format</option>
                                    <option value="PDF" class="bg-slate-900">PDF</option>
                                    <option value="CD" class="bg-slate-900">CD</option>
                                    <option value="HardCopy" class="bg-slate-900">HardCopy</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Quantity</label>
                                <input type="number" name="quantity" min="1" value="1" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300" />
                            </div>

                            <!-- Shipping Charge -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Shipping Charge</label>
                                <input type="number" step="0.01" name="shippingCharge" value="0.00" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300" />
                            </div>

                            <!-- Total Amount -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Total Amount</label>
                                <input type="number" step="0.01" name="totalAmount" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300" />
                            </div>

                            <!-- Order Date -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Order Date</label>
                                <input type="datetime-local" name="orderDate" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300" />
                            </div>

                            <!-- Payment Status -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Payment Status</label>
                                <select name="paymentStatus" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300">
                                    <option value="" class="bg-slate-900">Select Status</option>
                                    <option value="Pending" class="bg-slate-900">Pending</option>
                                    <option value="Paid" class="bg-slate-900">Paid</option>
                                </select>
                            </div>

                            <!-- Delivery Address -->
                            <div>
                                <label class="block mb-2 font-semibold text-slate-300">Delivery Address</label>
                                <textarea name="deliveryAddress" required class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700/50 text-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300"></textarea>
                            </div>

                            <!-- Submit -->
                            <div class="text-center mt-8">
                                <input type="submit" value="Add Order" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white shadow-2xl shadow-emerald-500/30 py-4 text-lg font-semibold rounded-xl transition-all duration-300 hover:scale-105 cursor-pointer" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });

        // Parallax background effect
        let scrollY = 0;
        
        function updateParallax() {
            scrollY = window.scrollY;
            
            const bg1 = document.getElementById('bg1');
            const bg2 = document.getElementById('bg2');
            const bg3 = document.getElementById('bg3');
            
            if (bg1) bg1.style.transform = `translateY(${scrollY * 0.1}px)`;
            if (bg2) bg2.style.transform = `translateY(${scrollY * -0.15}px)`;
            if (bg3) bg3.style.transform = `translateY(${scrollY * 0.05}px)`;
        }

        window.addEventListener('scroll', updateParallax);
        updateParallax();
    </script>
</body>
</html>
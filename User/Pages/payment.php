<?php
session_start();
include "../../Config/db.php"; 
if (empty($_SESSION['cart']) && !isset($_GET['success'])) {
    header("Location: cart.php"); 
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Auth/login.php"); 
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Determine if the order contains PDF and/or non-PDF items
$has_pdf = false;
$has_non_pdf = false;
foreach ($_SESSION['cart'] as $item) {
    if ($item['format'] === 'PDF') {
        $has_pdf = true;
    } else {
        $has_non_pdf = true;
    }
    if ($has_pdf && $has_non_pdf) {
        break;
    }
}
// Apply shipping fee and address requirements for non-PDF or mixed orders
$requires_shipping = $has_non_pdf;
$shipping = $requires_shipping ? 5.99 : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zipCode = $_POST['zipCode'] ?? '';
    $paymentMethod = $_POST['paymentMethod'] ?? '';
    $cardNumber = $_POST['cardNumber'] ?? '';
    $expiryDate = $_POST['expiryDate'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $cardName = $_POST['cardName'] ?? '';

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($paymentMethod)) {
        die("All required fields must be filled.");
    }

    // Validate shipping address for non-PDF or mixed orders
    if ($requires_shipping && (empty($address) || empty($city) || empty($state) || empty($zipCode))) {
        die("Shipping address is required for orders containing physical items.");
    }

    // Additional validation for CreditCard payment
    if ($paymentMethod === 'CreditCard' && (empty($cardNumber) || empty($expiryDate) || empty($cvv) || empty($cardName))) {
        die("All credit card fields are required for Credit Card payment.");
    }

    // Validate payment method based on order type
    $allowedPaymentMethods = $has_pdf ? ['CreditCard'] : ['CreditCard', 'COD', 'DD', 'Cheque'];
    if (!in_array($paymentMethod, $allowedPaymentMethods)) {
        die("Invalid payment method selected.");
    }

    // Calculate subtotal
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * ($item['format'] === 'PDF' ? 1 : $item['qty']);
    }
    $deliveryAddress = $requires_shipping ? "$address, $city, $state $zipCode" : null;

    $conn->begin_transaction();

    try {
        // Update user information
        $sql = "UPDATE users SET FullName = ?, City = ?, State = ?, ZipCode = ?, PhoneNumber = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $fullName = $firstName . ' ' . $lastName;
        $city = $requires_shipping ? $city : '';
        $state = $requires_shipping ? $state : '';
        $zipCode = $requires_shipping ? $zipCode : '';
        $stmt->bind_param("sssssi", $fullName, $city, $state, $zipCode, $phone, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Store order details in session for confirmation page
        $_SESSION['order_details'] = [
            'items' => $_SESSION['cart'],
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'address' => $deliveryAddress,
            'order_date' => date('Y-m-d'),
            'user_name' => $fullName,
            'payment_method' => $paymentMethod,
            'order_ids' => [] // To store OrderIDs
        ];

        foreach ($_SESSION['cart'] as $item) {
            $book_id = $item['id'];
            $qty = $item['format'] === 'PDF' ? 1 : $item['qty'];
            $format = ucfirst($item['format'] === 'PDF' ? 'PDF' : ($item['format'] === 'CD' ? 'CD' : 'HardCopy'));
            $total_amount = $item['price'] * $qty;        
            $paymentStatus = ($paymentMethod === 'CreditCard') ? 'Paid' : 'Pending';

            // Handle confirmation and token based on payment method
            if ($paymentMethod === 'CreditCard') {
                // For credit card: confirmation = 1, no token
                $confirmation = 1;
                $confirmation_token = null;
                $sql = "INSERT INTO orders (UserID, BookID, Format, Quantity, ShippingCharge, TotalAmount, DeliveryAddress, PaymentStatus, PaymentMethod, confirmation, confirmation_token) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisiddsssis", $_SESSION['user_id'], $book_id, $format, $qty, $shipping, $total_amount, $deliveryAddress, $paymentStatus, $paymentMethod, $confirmation, $confirmation_token);
            } else {
                // For other methods: confirmation = 0, generate token
                $confirmation_token = bin2hex(random_bytes(16));
                $sql = "INSERT INTO orders (UserID, BookID, Format, Quantity, ShippingCharge, TotalAmount, DeliveryAddress, PaymentStatus, PaymentMethod, confirmation, confirmation_token) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisiddssss", $_SESSION['user_id'], $book_id, $format, $qty, $shipping, $total_amount, $deliveryAddress, $paymentStatus, $paymentMethod, $confirmation_token);
            }
            
            $stmt->execute();
            $order_id = $conn->insert_id; // Get the inserted OrderID
            // Store order_id and token (or null for credit card)
            $_SESSION['order_details']['order_ids'][] = [
                'order_id' => $order_id,
                'confirmation_token' => $confirmation_token
            ];
            $stmt->close();

            // Update stock for hardcover or CD
            if ($format !== 'PDF') {
                $sql = "UPDATE books SET Stock = Stock - ? WHERE BookID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $qty, $book_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Commit transaction
        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        // Redirect based on payment method
        if ($paymentMethod === 'CreditCard') {
            header("Location: credit_card_confirmation.php?success=1");
        } else {
            // For non-credit card payments, redirect based on order type
            if ($has_pdf && !$has_non_pdf) {
                header("Location: credit_card_confirmation?success=1");
            } else {
                header("Location: order_confirmation.php?success=1");
            }
        }
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing order: " . $e->getMessage());
    }
}
?>

<!-- HTML and JavaScript remain unchanged -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <title>Payment - E - Books</title>
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
        .form-input:focus { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .payment-card { transition: all 0.3s ease; }
        .payment-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background-color: #10b981; border-radius: 8px; border: 2px solid #1e293b; }
        ::-webkit-scrollbar-thumb:hover { background-color: #059669; }
        .payment-option { transition: all 0.3s ease; }
        .payment-option input:checked + label { background-color: #10b981; color: white; }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex mb-8 animate-fade-in" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php" class="text-slate-300 hover:text-emerald-400"><i class="fas fa-home mr-2"></i>Home</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-400 mx-2"></i>
                        <a href="cart.php" class="text-slate-300 hover:text-emerald-400">Cart</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-slate-400 mx-2"></i>
                        <span class="text-emerald-400">Payment</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="text-center mb-12 animate-fade-in">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4"><i class="fas fa-credit-card mr-4"></i>Secure Checkout</h1>
            <p class="text-xl text-slate-400">Complete your purchase safely and securely</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="glass-effect rounded-2xl p-8 payment-card animate-fade-in">
                    <h2 class="text-2xl font-bold text-white mb-6"><i class="fas fa-user mr-2"></i>Billing Information</h2>
                    <form id="payment-form" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-white mb-2">First Name</label>
                                <input type="text" id="firstName" name="firstName" required class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-white mb-2">Last Name</label>
                                <input type="text" id="lastName" name="lastName" required class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-white mb-2">Email Address</label>
                            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300 cursor-not-allowed">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-white mb-2">Phone Number</label>
                            <input type="number" id="phone" name="phone" required class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                        </div>
                        <div id="shipping-address-section" class="<?php echo $requires_shipping ? '' : 'hidden'; ?> border-t border-slate-700/50 pt-6">
                            <h3 class="text-xl font-semibold text-white mb-4"><i class="fas fa-map-marker-alt mr-2"></i>Shipping Address</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-white mb-2">Street Address</label>
                                    <input type="text" id="address" name="address" <?php echo $requires_shipping ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-white mb-2">City</label>
                                        <input type="text" id="city" name="city" <?php echo $requires_shipping ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                    </div>
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-white mb-2">State</label>
                                        <input type="text" id="state" name="state" <?php echo $requires_shipping ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                    </div>
                                    <div>
                                        <label for="zipCode" class="block text-sm font-medium text-white mb-2">ZIP Code</label>
                                        <input type="number" id="zipCode" name="zipCode" <?php echo $requires_shipping ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-700/50 pt-6">
                            <h3 class="text-xl font-semibold text-white mb-4"><i class="fas fa-credit-card mr-2"></i>Payment Method</h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="payment-option">
                                        <input type="radio" id="creditCard" name="paymentMethod" value="CreditCard" checked class="hidden peer">
                                        <label for="creditCard" class="flex items-center px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white cursor-pointer hover:bg-emerald-500/20 transition-all duration-300">
                                            <i class="fas fa-credit-card mr-2"></i> Credit Card
                                        </label>
                                    </div>
                                    <div class="payment-option <?php echo $has_pdf ? 'hidden' : ''; ?>">
                                        <input type="radio" id="cod" name="paymentMethod" value="COD" class="hidden peer">
                                        <label for="cod" class="flex items-center px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white cursor-pointer hover:bg-emerald-500/20 transition-all duration-300">
                                            <i class="fas fa-money-bill-wave mr-2"></i> Cash on Delivery
                                        </label>
                                    </div>
                                    <div class="payment-option <?php echo $has_pdf ? 'hidden' : ''; ?>">
                                        <input type="radio" id="dd" name="paymentMethod" value="DD" class="hidden peer">
                                        <label for="dd" class="flex items-center px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white cursor-pointer hover:bg-emerald-500/20 transition-all duration-300">
                                            <i class="fas fa-bank mr-2"></i> Demand Draft
                                        </label>
                                    </div>
                                    <div class="payment-option <?php echo $has_pdf ? 'hidden' : ''; ?>">
                                        <input type="radio" id="cheque" name="paymentMethod" value="Cheque" class="hidden peer">
                                        <label for="cheque" class="flex items-center px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white cursor-pointer hover:bg-emerald-500/20 transition-all duration-300">
                                            <i class="fas fa-money-check mr-2"></i> Cheque
                                        </label>
                                    </div>
                                </div>
                                <div id="credit-card-details" class="space-y-4">
                                    <div>
                                        <label for="cardNumber" class="block text-sm font-medium text-white mb-2">Card Number</label>
                                        <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" <?php echo $has_pdf ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="expiryDate" class="block text-sm font-medium text-white mb-2">Expiry Date</label>
                                            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" <?php echo $has_pdf ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                        </div>
                                        <div>
                                            <label for="cvv" class="block text-sm font-medium text-white mb-2">CVV</label>
                                            <input type="text" id="cvv" name="cvv" placeholder="123" <?php echo $has_pdf ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="cardName" class="block text-sm font-medium text-white mb-2">Name on Card</label>
                                        <input type="text" id="cardName" name="cardName" <?php echo $has_pdf ? 'required' : ''; ?> class="form-input w-full px-4 py-3 rounded-lg bg-slate-800/30 border border-slate-700/50 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="place-order-btn" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 px-6 rounded-lg font-semibold text-lg transition-all duration-300 shadow-lg">
                            <i class="fas fa-lock mr-2"></i>Place Order
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="glass-effect rounded-2xl p-6 payment-card animate-fade-in sticky top-24">
                    <h2 class="text-2xl font-bold text-white mb-6"><i class="fas fa-receipt mr-2"></i>Order Summary</h2>
                    <div id="order-items" class="space-y-4 mb-6">
                        <?php if (empty($_SESSION['cart'])): ?>
                            <p class="text-slate-400">No items in cart</p>
                        <?php else: ?>
                            <?php
                            $subtotal = 0;
                            foreach ($_SESSION['cart'] as $item) {
                                $qty = $item['format'] === 'PDF' ? 1 : $item['qty'];
                                $subtotal += $item['price'] * $qty;
                            ?>
                                <div class="flex justify-between items-center py-2">
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium"><?= htmlspecialchars($item['title']) ?></h4>
                                        <p class="text-slate-400 text-sm">Format: <?= ucfirst($item['format']) ?> | Qty: <?= $qty ?></p>
                                    </div>
                                    <span class="text-white font-semibold">$<?= number_format($item['price'] * $qty, 2) ?></span>
                                </div>
                            <?php } ?>
                        <?php endif; ?>
                    </div>
                    <div class="border-t border-slate-700/50 pt-4 space-y-2">
                        <div class="flex justify-between text-white">
                            <span>Subtotal:</span>
                            <span id="subtotal">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-white">
                            <span>Shipping:</span>
                            <span id="shipping"><?php echo $requires_shipping ? '$5.99' : '$0'; ?></span>
                        </div>
                        <div class="border-t border-slate-700/50 pt-2 flex justify-between text-xl font-bold text-white">
                            <span>Total:</span>
                            <span id="total">$<?= number_format($subtotal + $shipping, 2) ?></span>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm text-slate-400"><i class="fas fa-shield-alt mr-1"></i>Your payment information is secure and encrypted</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('input[name="paymentMethod"]').forEach((radio) => {
            radio.addEventListener('change', function() {
                const creditCardDetails = document.getElementById('credit-card-details');
                if (this.value === 'CreditCard') {
                    creditCardDetails.style.display = 'block';
                    document.getElementById('cardNumber').setAttribute('required', 'required');
                    document.getElementById('expiryDate').setAttribute('required', 'required');
                    document.getElementById('cvv').setAttribute('required', 'required');
                    document.getElementById('cardName').setAttribute('required', 'required');
                } else {
                    creditCardDetails.style.display = 'none';
                    document.getElementById('cardNumber').removeAttribute('required');
                    document.getElementById('expiryDate').removeAttribute('required');
                    document.getElementById('cvv').removeAttribute('required');
                    document.getElementById('cardName').removeAttribute('required');
                }
            });
        });

        <?php if ($has_pdf): ?>
            document.getElementById('credit-card-details').style.display = 'block';
            document.getElementById('cardNumber').setAttribute('required', 'required');
            document.getElementById('expiryDate').setAttribute('required', 'required');
            document.getElementById('cvv').setAttribute('required', 'required');
            document.getElementById('cardName').setAttribute('required', 'required');
        <?php else: ?>
            document.querySelector('input[name="paymentMethod"]:checked').dispatchEvent(new Event('change'));
        <?php endif; ?>

        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        document.getElementById('expiryDate').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 4);
        });

        const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + (item.format === 'PDF' ? 1 : item.qty), 0);
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = totalItems;
            }
            const mobileCartText = document.querySelector('#mobile-menu a[href="cart.php"]');
            if (mobileCartText) {
                mobileCartText.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0h9"></path>
                    </svg>
                    Cart (${totalItems})
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
<?php
require 'config.php';

// 1. Authentication Check: User must be logged in to checkout
if (!isset($_SESSION['user_id'])) {
    // Save intended destination so they come back after login
    $_SESSION['redirect_to'] = 'checkout.php'; 
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Cart Check: Prevent checkout if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// 3. Calculate Totals Securely from Database
$cart_items = [];
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    $qty = $_SESSION['cart'][$product['id']];
    $product['quantity'] = $qty;
    $product['line_total'] = $product['price'] * $qty;
    $subtotal += $product['line_total'];
    $cart_items[] = $product;
}

$shipping_cost = ($subtotal >= 150.00) ? 0.00 : 15.00;
$total = $subtotal + $shipping_cost;

// 4. Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        // Insert order into the database
        $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Pending')");
        $order_stmt->execute([$user_id, $total]);
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Redirect to buyer's dashboard with a success flag
        header("Location: profile.php?checkout=success");
        exit;
    } catch (PDOException $e) {
        $error = "Payment failed. Please try again.";
    }
}

// Fetch user info for pre-filling the form
$user_stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Black Ivie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] },
                    colors: { brand: '#0a0a0a', stone: '#f4f4f0' }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-brand font-sans antialiased min-h-screen flex flex-col">

    <!-- Minimal Header -->
    <header class="py-6 border-b border-gray-100 text-center sticky top-0 bg-white/90 backdrop-blur-md z-50">
        <a href="index.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
        <div class="mt-2 text-xs uppercase tracking-widest text-gray-400 font-bold flex items-center justify-center">
            <i class="fa-solid fa-lock mr-2 text-green-700"></i> Secure Checkout Session
        </div>
    </header>

    <main class="flex-grow flex flex-col lg:flex-row">
        
        <!-- Left Side: Checkout Form -->
        <div class="w-full lg:w-3/5 p-8 lg:p-16 lg:pl-32 xl:pl-48">
            
            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded mb-6 text-sm flex items-center">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="checkout.php" method="POST" class="space-y-12 max-w-2xl">
                
                <!-- Contact Info -->
                <section>
                    <h2 class="text-xl font-serif border-b border-gray-200 pb-2 mb-6">Contact Information</h2>
                    <div class="space-y-4">
                        <input type="text" value="<?php echo htmlspecialchars($user['fullname']); ?>" class="w-full p-3 border border-gray-300 rounded focus:ring-black focus:border-black outline-none bg-gray-50 text-gray-500" readonly>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-3 border border-gray-300 rounded focus:ring-black focus:border-black outline-none bg-gray-50 text-gray-500" readonly>
                    </div>
                </section>

                <!-- Shipping Details -->
                <section>
                    <h2 class="text-xl font-serif border-b border-gray-200 pb-2 mb-6">Shipping Address</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" placeholder="First Name" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                        <input type="text" placeholder="Last Name" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                        <input type="text" placeholder="Address Line 1" required class="col-span-2 p-3 border border-gray-300 rounded focus:border-black outline-none">
                        <input type="text" placeholder="City" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                        <input type="text" placeholder="ZIP / Postal Code" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                        <select class="col-span-2 p-3 border border-gray-300 rounded focus:border-black outline-none bg-white">
                            <option>United States</option>
                            <option>United Kingdom</option>
                            <option>Canada</option>
                            <option>Australia</option>
                        </select>
                    </div>
                </section>

                <!-- Payment Details (Dummy UI for aesthetics) -->
                <section>
                    <h2 class="text-xl font-serif border-b border-gray-200 pb-2 mb-6">Payment</h2>
                    <div class="bg-stone p-6 border border-gray-200 rounded">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-semibold uppercase tracking-widest">Credit Card</span>
                            <div class="flex space-x-2 text-xl">
                                <i class="fa-brands fa-cc-visa"></i>
                                <i class="fa-brands fa-cc-mastercard"></i>
                                <i class="fa-brands fa-cc-amex"></i>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <input type="text" placeholder="Card Number" required class="w-full p-3 border border-gray-300 rounded focus:border-black outline-none">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" placeholder="MM / YY" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                                <input type="text" placeholder="CVC" required class="col-span-1 p-3 border border-gray-300 rounded focus:border-black outline-none">
                            </div>
                        </div>
                    </div>
                </section>

                <button type="submit" name="place_order" class="w-full bg-brand text-white py-5 text-sm uppercase tracking-widest font-bold hover:bg-gray-800 transition-all shadow-lg active:scale-[0.98]">
                    Pay $<?php echo number_format($total, 2); ?>
                </button>
            </form>
            
            <p class="mt-8 text-xs text-gray-400 text-center max-w-2xl">By placing your order, you agree to Black Ivie's Privacy Policy and Terms of Use. Payments are encrypted and secure.</p>
        </div>

        <!-- Right Side: Order Summary -->
        <div class="w-full lg:w-2/5 bg-stone border-l border-gray-200 p-8 lg:p-16 lg:pr-32 xl:pr-48 hidden md:block">
            <h2 class="text-xl font-serif border-b border-gray-300 pb-4 mb-6">Order Summary</h2>
            
            <!-- Items Loop -->
            <div class="space-y-4 mb-8 max-h-[40vh] overflow-y-auto pr-2">
                <?php foreach($cart_items as $item): ?>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-20 bg-white relative border border-gray-200 flex-shrink-0">
                        <span class="absolute -top-2 -right-2 bg-gray-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            <?php echo $item['quantity']; ?>
                        </span>
                        <?php $img = !empty($item['image']) ? "uploads/".$item['image'] : "https://images.unsplash.com/photo-1594035910387-fea47794261f?w=100&q=80"; ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow">
                        <h4 class="font-serif font-bold text-sm"><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="text-xs text-gray-500">$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="font-semibold text-sm">
                        $<?php echo number_format($item['line_total'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Totals -->
            <div class="space-y-3 text-sm border-t border-gray-300 pt-6">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Shipping</span>
                    <?php if($shipping_cost == 0): ?>
                        <span class="text-xs uppercase tracking-widest font-bold text-green-600">Free</span>
                    <?php else: ?>
                        <span class="font-semibold">$<?php echo number_format($shipping_cost, 2); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-between items-end border-t border-black pt-6 mt-6">
                <span class="text-lg font-serif">Total</span>
                <span class="text-3xl font-semibold tracking-tight">$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

    </main>
</body>
</html>
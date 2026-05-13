<?php
require 'config.php';

// 1. Authentication Check: Kick out unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Details securely
$stmt = $pdo->prepare("SELECT fullname, email, created_at FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 3. Fetch Recent Orders for this user 
// Corrected: Uses 'user_id' to match the user to their orders
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC LIMIT 5");
$order_stmt->execute(['id' => $user_id]);
$recent_orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Cart Items for Navbar
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Black Ivie</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: '#0a0a0a',
                        stone: '#f4f4f0',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-stone text-brand font-sans antialiased min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- Premium Navbar -->
    <nav class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-brand hover:text-gray-600 focus:outline-none">
                        <i class="fa-solid fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                        <i class="fa-solid fa-xmark text-2xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
                <div class="hidden md:flex space-x-8 items-center w-1/3">
                    <a href="index.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">Shop</a>
                    <a href="collections.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">Collections</a>
                </div>
                <div class="flex-shrink-0 flex justify-center w-1/3">
                    <a href="index.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
                </div>
                <div class="flex items-center justify-end space-x-6 w-1/3 hidden sm:flex">
                    <a href="cart.php" class="text-brand hover:text-gray-500 transition-colors relative flex items-center">
                        <i class="fa-solid fa-bag-shopping text-xl"></i>
                        <?php if($cart_count > 0): ?>
                            <span class="absolute -top-1 -right-2 bg-brand text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white absolute w-full h-screen shadow-xl flex flex-col pt-8 px-6 space-y-6" x-cloak>
            <a href="index.php" class="text-2xl font-serif border-b border-gray-100 pb-4">Shop All</a>
            <a href="collections.php" class="text-2xl font-serif border-b border-gray-100 pb-4">Collections</a>
            <a href="profile.php" class="text-2xl font-serif border-b border-gray-100 pb-4">My Account</a>
        </div>
    </nav>

    <!-- Main Dashboard Area -->
    <main class="flex-grow max-w-7xl mx-auto w-full py-16 px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Header -->
        <div class="mb-16 border-b border-gray-300 pb-8 flex justify-between items-end">
            <div>
                <p class="text-sm uppercase tracking-widest text-gray-500 mb-2">Client Portal</p>
                <h1 class="text-4xl md:text-5xl font-serif">Welcome, <?php echo htmlspecialchars(explode(' ', trim($user['fullname']))[0]); ?>.</h1>
            </div>
            <a href="logout.php" class="hidden md:inline-block text-sm uppercase tracking-widest font-bold text-gray-500 hover:text-red-600 transition-colors">
                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Sign Out
            </a>
        </div>

        <div class="flex flex-col md:flex-row gap-12 lg:gap-20">
            
            <!-- Left Sidebar Navigation -->
            <aside class="w-full md:w-1/4">
                <nav class="space-y-6">
                    <a href="profile.php" class="block text-lg font-serif border-l-2 border-black pl-4">Dashboard</a>
                    <a href="#" class="block text-lg font-serif text-gray-400 hover:text-black border-l-2 border-transparent hover:border-gray-300 pl-4 transition-colors">Order History</a>
                    <a href="edit-profile.php" class="block text-lg font-serif text-gray-400 hover:text-black border-l-2 border-transparent hover:border-gray-300 pl-4 transition-colors">Account Details</a>
                    <a href="logout.php" class="block text-lg font-serif text-red-400 hover:text-red-600 border-l-2 border-transparent hover:border-red-300 pl-4 transition-colors md:hidden mt-12">Sign Out</a>
                </nav>
            </aside>

            <!-- Right Content Area -->
            <div class="w-full md:w-3/4 space-y-16">
                
                <!-- Account Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Profile Card -->
                    <div class="bg-white p-8 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-sm uppercase tracking-widest font-bold">Profile Details</h3>
                            <i class="fa-regular fa-user text-gray-400"></i>
                        </div>
                        <p class="font-serif text-xl mb-1"><?php echo htmlspecialchars($user['fullname']); ?></p>
                        <p class="text-gray-500 font-light mb-6"><?php echo htmlspecialchars($user['email']); ?></p>
                        <a href="edit-profile.php" class="text-xs uppercase tracking-widest font-bold border-b border-black pb-1 hover:text-gray-500 transition-colors">Edit Details</a>
                    </div>

                    <!-- Member Status Card -->
                    <div class="bg-brand text-white p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-800 pb-4">
                            <h3 class="text-sm uppercase tracking-widest font-bold text-gray-400">Status</h3>
                            <i class="fa-solid fa-crown text-amber-500"></i>
                        </div>
                        <p class="font-serif text-xl mb-1">Signature Member</p>
                        <!-- Fallback to current date if created_at is empty/null -->
                        <p class="text-gray-400 font-light text-sm mb-6">Member since <?php echo !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : date('F Y'); ?></p>
                        <a href="collections.php" class="text-xs uppercase tracking-widest font-bold border-b border-white pb-1 hover:text-gray-300 transition-colors">Explore Exclusives</a>
                    </div>
                </div>

                <!-- Order History Section -->
                <section>
                    <div class="flex justify-between items-end mb-8">
                        <h2 class="text-3xl font-serif">Recent Orders</h2>
                        <a href="#" class="text-xs uppercase tracking-widest font-bold border-b border-black pb-1 hover:text-gray-500 transition-colors hidden sm:block">View All</a>
                    </div>

                    <?php if(empty($recent_orders)): ?>
                        <div class="bg-white p-12 text-center border border-gray-100 shadow-sm">
                            <i class="fa-solid fa-box-open text-3xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-serif mb-2">No orders yet.</h3>
                            <p class="text-gray-500 font-light mb-6">You haven't placed any orders with us.</p>
                            <a href="index.php" class="inline-block bg-brand text-white px-8 py-3 text-sm uppercase tracking-widest font-bold hover:bg-gray-800 transition-colors">
                                Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-stone/50 text-xs uppercase tracking-widest text-gray-500 border-b border-gray-200">
                                            <th class="py-4 px-6 font-semibold">Order ID</th>
                                            <th class="py-4 px-6 font-semibold">Date</th>
                                            <th class="py-4 px-6 font-semibold">Status</th>
                                            <th class="py-4 px-6 font-semibold text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach($recent_orders as $order): ?>
                                        <tr class="hover:bg-stone/30 transition-colors group cursor-pointer">
                                            <!-- Corrected: Pulling order_id specifically -->
                                            <td class="py-5 px-6 font-medium text-sm">
                                                #BI-<?php echo str_pad($order['order_id'], 5, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="py-5 px-6 text-gray-500 text-sm"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td class="py-5 px-6 text-sm">
                                                <?php 
                                                    $status_color = 'text-gray-600 bg-gray-100';
                                                    if(isset($order['status'])) {
                                                        if($order['status'] == 'Shipped') $status_color = 'text-blue-600 bg-blue-50';
                                                        if($order['status'] == 'Delivered') $status_color = 'text-green-600 bg-green-50';
                                                    }
                                                ?>
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_color; ?>">
                                                    <?php echo isset($order['status']) ? htmlspecialchars($order['status']) : 'Pending'; ?>
                                                </span>
                                            </td>
                                            <td class="py-5 px-6 text-right font-semibold">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                
            </div>
        </div>
    </main>

    <!-- Minimal Footer -->
    <footer class="bg-brand text-white py-8 border-t border-gray-900 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center md:text-left flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 font-light text-sm">&copy; <?php echo date('Y'); ?> Black Ivie. All Rights Reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0 text-gray-400 text-sm">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
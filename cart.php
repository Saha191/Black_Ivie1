<?php
require 'config.php'; 

// 1. Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Handle Cart Actions (Add, Remove, Update)
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? 0;

if ($action === 'add' && $id) {
    // If item exists, increase quantity; otherwise, set to 1
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: cart.php");
    exit;
}

if ($action === 'remove' && $id) {
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

if ($action === 'update' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $prod_id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$prod_id]);
        } else {
            $_SESSION['cart'][$prod_id] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}

// 3. Fetch Cart Products from Database
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    // Create placeholders dynamically based on how many items are in the cart
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map quantities and calculate totals
    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $product['quantity'] = $qty;
        $product['line_total'] = $product['price'] * $qty;
        $subtotal += $product['line_total'];
        $cart_items[] = $product;
    }
}

// 4. Calculate Shipping & Final Total
$shipping_threshold = 150.00;
$shipping_cost = 15.00;
$is_free_shipping = $subtotal >= $shipping_threshold;
$final_shipping = $is_free_shipping ? 0 : $shipping_cost;
$total = $subtotal > 0 ? ($subtotal + $final_shipping) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bag | Black Ivie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    
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
<body class="bg-white text-brand font-sans antialiased flex flex-col min-h-screen">

    <!-- Minimal Header -->
    <header class="py-8 px-4 sm:px-6 lg:px-8 border-b border-gray-100 flex justify-between items-center">
        <a href="index.php" class="text-sm font-semibold uppercase tracking-widest text-gray-500 hover:text-black transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Continue Shopping
        </a>
        <a href="index.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
        <div class="w-[130px]"></div> <!-- Spacer for perfect centering -->
    </header>

    <main class="flex-grow max-w-7xl mx-auto w-full py-16 px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-4xl md:text-5xl font-serif mb-12 text-center">Your Shopping Bag</h1>

        <?php if(empty($cart_items)): ?>
            <!-- Empty State -->
            <div class="text-center py-20 bg-stone/30 rounded-2xl">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <i class="fa-solid fa-bag-shopping text-2xl text-gray-400"></i>
                </div>
                <h2 class="text-2xl font-serif mb-4">Your bag is currently empty.</h2>
                <p class="text-gray-500 font-light mb-8">Discover our signature collections and find your new scent.</p>
                <a href="index.php" class="inline-block bg-brand text-white px-10 py-4 text-sm uppercase tracking-widest font-semibold hover:bg-gray-900 transition-colors">
                    Explore Fragrances
                </a>
            </div>
        <?php else: ?>
            
            <div class="flex flex-col lg:flex-row gap-16">
                <!-- Left Column: Cart Items -->
                <div class="w-full lg:w-2/3">
                    <form action="cart.php" method="POST" id="update-cart-form">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="hidden sm:grid grid-cols-12 gap-4 text-xs uppercase tracking-widest text-gray-400 font-semibold border-b border-gray-200 pb-4 mb-8">
                            <div class="col-span-6">Product</div>
                            <div class="col-span-3 text-center">Quantity</div>
                            <div class="col-span-3 text-right">Total</div>
                        </div>

                        <div class="space-y-8">
                            <?php foreach($cart_items as $item): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center group border-b border-gray-100 sm:border-none pb-8 sm:pb-0">
                                
                                <!-- Product Details -->
                                <div class="col-span-1 sm:col-span-6 flex items-center gap-6">
                                    <div class="w-24 h-32 bg-stone flex-shrink-0 relative overflow-hidden">
                                        <?php $img_src = !empty($item['image']) ? "uploads/".$item['image'] : "https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=200&auto=format&fit=crop"; ?>
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-[0.2em] mb-1"><?php echo htmlspecialchars($item['category']); ?></p>
                                        <h3 class="font-serif text-lg leading-tight mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-sm text-gray-500 font-medium">$<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="col-span-1 sm:col-span-3 flex justify-start sm:justify-center items-center">
                                    <div class="flex items-center border border-gray-300 rounded-sm">
                                        <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); document.getElementById('update-cart-form').submit();" class="px-3 py-1 text-gray-500 hover:text-black transition-colors">-</button>
                                        
                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" class="w-12 text-center text-sm focus:outline-none appearance-none bg-transparent" onchange="document.getElementById('update-cart-form').submit();">
                                        
                                        <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); document.getElementById('update-cart-form').submit();" class="px-3 py-1 text-gray-500 hover:text-black transition-colors">+</button>
                                    </div>
                                </div>

                                <!-- Item Total & Remove -->
                                <div class="col-span-1 sm:col-span-3 flex justify-between sm:justify-end items-center mt-4 sm:mt-0">
                                    <span class="font-semibold sm:hidden uppercase tracking-widest text-xs text-gray-500">Total:</span>
                                    <div class="flex items-center gap-6">
                                        <span class="font-semibold">$<?php echo number_format($item['line_total'], 2); ?></span>
                                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="text-gray-400 hover:text-red-500 transition-colors" title="Remove Item">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-stone/50 p-8 rounded-sm sticky top-28">
                        <h2 class="text-xl font-serif mb-6 border-b border-gray-200 pb-4">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-light">Subtotal</span>
                                <span class="font-semibold">$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-light">Estimated Shipping</span>
                                <?php if($is_free_shipping): ?>
                                    <span class="text-xs uppercase tracking-widest font-bold text-green-600">Free</span>
                                <?php else: ?>
                                    <span class="font-semibold">$<?php echo number_format($shipping_cost, 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if(!$is_free_shipping): ?>
                                <p class="text-xs text-gray-500 italic">Spend $<?php echo number_format($shipping_threshold - $subtotal, 2); ?> more to unlock free shipping.</p>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-between items-end border-t border-gray-200 pt-6 mb-8">
                            <span class="text-lg font-serif">Total</span>
                            <span class="text-2xl font-semibold tracking-tight">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <!-- Proceed to Checkout -->
                        <a href="#" class="w-full flex justify-center items-center py-4 px-4 bg-brand text-white text-sm uppercase tracking-widest font-bold hover:bg-gray-900 transition-colors shadow-lg active:scale-[0.98]">
                            Secure Checkout <i class="fa-solid fa-lock ml-2 text-xs opacity-70"></i>
                        </a>

                        <!-- Trust Badges -->
                        <div class="mt-8 pt-6 border-t border-gray-200/50 text-center">
                            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Accepted Payment Methods</p>
                            <div class="flex justify-center space-x-4 text-2xl text-gray-300">
                                <i class="fa-brands fa-cc-visa hover:text-gray-600 transition-colors"></i>
                                <i class="fa-brands fa-cc-mastercard hover:text-gray-600 transition-colors"></i>
                                <i class="fa-brands fa-cc-apple-pay hover:text-gray-600 transition-colors"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </main>

</body>
</html>
<?php
require 'config.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Check if product actually exists and is in stock
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
        // Redirect to prevent form resubmission on refresh
        header("Location: shop.php?added=true");
        exit;
    }
}

// Fetch all products from the database
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Cart Items for Navbar
$cart_count = array_sum($_SESSION['cart']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Collection | Black Ivie</title>
    
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
                    <a href="shop.php" class="text-sm tracking-widest uppercase text-black font-bold border-b-2 border-black pb-1">Shop</a>
                    <a href="#" class="text-sm tracking-widest uppercase text-gray-500 hover:text-black transition-colors pb-1">Collections</a>
                </div>
                <div class="flex-shrink-0 flex justify-center w-1/3">
                    <a href="shop.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
                </div>
                <div class="flex items-center justify-end space-x-6 w-1/3 hidden sm:flex">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php $profile_link = ($_SESSION['role'] === 'admin') ? 'admin.php' : 'profile.php'; ?>
                        <a href="<?php echo $profile_link; ?>" class="text-sm tracking-widest uppercase text-gray-500 hover:text-black transition-colors">Account</a>
                    <?php else: ?>
                        <a href="login.php" class="text-sm tracking-widest uppercase text-gray-500 hover:text-black transition-colors">Sign In</a>
                    <?php endif; ?>
                    
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
            <a href="shop.php" class="text-2xl font-serif border-b border-gray-100 pb-4">Shop All</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php $profile_link = ($_SESSION['role'] === 'admin') ? 'admin.php' : 'profile.php'; ?>
                <a href="<?php echo $profile_link; ?>" class="text-2xl font-serif border-b border-gray-100 pb-4">My Account</a>
                <a href="logout.php" class="text-2xl font-serif text-red-500 border-b border-gray-100 pb-4">Sign Out</a>
            <?php else: ?>
                <a href="login.php" class="text-2xl font-serif border-b border-gray-100 pb-4">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Success Toast Notification (Appears when item added to cart) -->
    <?php if(isset($_GET['added'])): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.opacity.duration.500ms class="fixed bottom-8 right-8 z-50 bg-brand text-white px-6 py-4 rounded shadow-2xl flex items-center gap-3">
        <i class="fa-solid fa-check text-green-400"></i>
        <p class="text-sm font-semibold uppercase tracking-widest">Added to Cart</p>
    </div>
    <?php endif; ?>

    <!-- Header Section -->
    <header class="py-16 md:py-24 px-4 text-center">
        <h1 class="text-5xl md:text-7xl font-serif mb-6">The Collection</h1>
        <p class="text-gray-500 max-w-2xl mx-auto text-lg font-light">Discover our curated selection of fine fragrances. Ethically sourced, masterfully blended, and designed to leave a lasting impression.</p>
    </header>

    <!-- Main Product Grid -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pb-24">
        
        <?php if(empty($products)): ?>
            <div class="text-center py-20">
                <i class="fa-solid fa-flask text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-serif mb-2">Our collection is currently being curated.</h3>
                <p class="text-gray-500">Please check back soon for our inaugural scent release.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-16">
                
                <?php foreach($products as $product): ?>
                <div class="group flex flex-col">
                    <!-- Image Area -->
                    <div class="relative bg-white aspect-[3/4] mb-6 overflow-hidden flex items-center justify-center">
                        <?php $img_src = (!empty($product['image']) && file_exists('uploads/'.$product['image'])) ? "uploads/".$product['image'] : "https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=600&auto=format&fit=crop"; ?>
                        
                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        
                        <!-- Stock Badge Overlay -->
                        <?php if($product['stock'] == 0): ?>
                            <div class="absolute top-4 left-4 bg-white/90 px-3 py-1 text-[10px] uppercase tracking-widest font-bold">Sold Out</div>
                        <?php elseif($product['stock'] < 5): ?>
                            <div class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 text-[10px] uppercase tracking-widest font-bold">Low Stock</div>
                        <?php endif; ?>

                        <!-- Quick Add Button (Appears on Hover) -->
                        <div class="absolute bottom-0 left-0 w-full p-4 translate-y-full opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                            <?php if($product['stock'] > 0): ?>
                            <form method="POST" action="shop.php">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="add_to_cart" class="w-full bg-brand text-white py-4 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 transition-colors shadow-lg active:scale-[0.98]">
                                    Add to Bag - $<?php echo number_format($product['price'], 2); ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <button disabled class="w-full bg-gray-200 text-gray-500 py-4 text-xs font-bold uppercase tracking-widest cursor-not-allowed">
                                    Currently Unavailable
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div class="text-center flex-grow flex flex-col">
                        <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                        <h3 class="text-2xl font-serif mb-2 leading-tight"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-500 text-sm font-light line-clamp-2 mb-4 flex-grow"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="font-semibold text-lg">$<?php echo number_format($product['price'], 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

    </main>

    <!-- Minimal Footer -->
    <footer class="bg-brand text-white py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h4 class="text-2xl font-serif tracking-tighter uppercase mb-4">Black Ivie</h4>
                <p class="text-gray-400 font-light text-sm max-w-xs mx-auto md:mx-0">Redefining luxury through olfactive art. Crafted with passion, worn with intention.</p>
            </div>
            <div class="flex flex-col space-y-2 text-sm text-gray-400">
                <a href="#" class="hover:text-white transition-colors">Shipping & Returns</a>
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
            <div class="flex justify-center md:justify-end space-x-6">
                <a href="#" class="text-gray-400 hover:text-white text-xl transition-colors"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-white text-xl transition-colors"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-white text-xl transition-colors"><i class="fa-brands fa-pinterest"></i></a>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-gray-900 text-center text-xs text-gray-500">
            &copy; <?php echo date('Y'); ?> Black Ivie. All Rights Reserved.
        </div>
    </footer>

</body>
</html>

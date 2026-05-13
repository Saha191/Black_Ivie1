<?php
require 'config.php'; // Ensure this contains session_start() and the $pdo connection

// Handle Search Query
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :search OR category LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Cart Items
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Black Ivie | Luxury Perfumes</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts for Luxury Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js for smooth mobile menu interactions -->
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
                        brand: '#0a0a0a', // Deepest black
                        stone: '#f4f4f0', // Warm luxury off-white
                    }
                }
            }
        }
    </script>
    <style>
        /* Hide scrollbar for clean horizontal scrolling in categories */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-stone text-brand font-sans antialiased" x-data="{ mobileMenuOpen: false, searchOpen: false }">

    <!-- Premium Sticky Navbar -->
    <nav class="bg-stone/90 backdrop-blur-md sticky top-0 z-50 border-b border-gray-200/50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-brand hover:text-gray-600 focus:outline-none">
                        <i class="fa-solid fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                        <i class="fa-solid fa-xmark text-2xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex space-x-8 items-center w-1/3">
                    <a href="shop.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">Shop</a>
                    <a href="collections.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">Collections</a>
                    <a href="about.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">About</a>
                </div>

                <!-- Logo -->
                <div class="flex-shrink-0 flex justify-center w-1/3">
                    <a href="index.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
                </div>

                <!-- Right Side Icons -->
                <div class="flex items-center justify-end space-x-6 w-1/3">
                    
                    <!-- Search Toggle -->
                    <button @click="searchOpen = !searchOpen" class="text-brand hover:text-gray-500 transition-colors hidden sm:block">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </button>

                    <!-- User Account -->
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="text-brand hover:text-gray-500 transition-colors hidden sm:block">
                        <i class="fa-regular fa-user text-lg"></i>
                    </a>

                    <!-- Cart -->
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

        <!-- Expandable Search Bar -->
        <div x-show="searchOpen" x-transition class="border-t border-gray-200 bg-white p-4 absolute w-full shadow-lg" x-cloak>
            <form action="index.php" method="GET" class="max-w-3xl mx-auto flex items-center">
                <i class="fa-solid fa-magnifying-glass text-gray-400 mr-3"></i>
                <input type="text" name="search" placeholder="Search fragrances, notes, or collections..." class="w-full bg-transparent border-none focus:ring-0 text-lg font-light outline-none" autofocus>
                <button type="submit" class="ml-4 text-sm font-bold uppercase tracking-widest border-b border-black pb-1 hover:text-gray-500">Discover</button>
            </form>
        </div>

        <!-- Mobile Menu Overlay -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white absolute w-full h-screen shadow-xl flex flex-col pt-8 px-6 space-y-6" x-cloak>
            <form action="index.php" method="GET" class="flex border-b border-gray-300 pb-2 mb-4">
                <input type="text" name="search" placeholder="Search..." class="w-full bg-transparent outline-none">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <a href="#" class="text-2xl font-serif border-b border-gray-100 pb-4">Shop All</a>
            <a href="collections.php" class="text-2xl font-serif border-b border-gray-100 pb-4">Collections</a>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="text-2xl font-serif border-b border-gray-100 pb-4">My Account</a>
        </div>
    </nav>

    <!-- Editorial Hero Section -->
    <header class="relative h-[80vh] flex items-center justify-center overflow-hidden">
        <img src="https://images.unsplash.com/photo-1615634260167-c8cdede054de?q=80&w=2000&auto=format&fit=crop" alt="Luxury Perfume" class="absolute inset-0 w-full h-full object-cover scale-105 transform motion-safe:animate-[pulse_10s_ease-in-out_infinite_alternate]">
        <div class="absolute inset-0 bg-black/40"></div>
        
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto text-white mt-16">
            <p class="text-xs md:text-sm uppercase tracking-[0.3em] mb-4 opacity-90">The Signature Collection</p>
            <h2 class="text-5xl md:text-7xl font-serif italic mb-6 leading-tight">Define your presence.</h2>
            <p class="text-lg md:text-xl font-light mb-10 max-w-2xl mx-auto opacity-90">Expertly crafted fragrances designed to leave an unforgettable impression.</p>
            <a href="#shop" class="inline-block bg-white text-brand px-10 py-4 text-sm uppercase tracking-widest font-semibold hover:bg-black hover:text-white transition-colors duration-300">
                Explore The Scents
            </a>
        </div>
    </header>

    <!-- Brand Bar -->
    <div class="bg-brand text-white py-4 overflow-hidden border-t border-gray-800">
        <div class="flex justify-center space-x-12 text-xs md:text-sm uppercase tracking-widest font-light opacity-80">
            <span class="flex items-center"><i class="fa-solid fa-leaf mr-2"></i> Cruelty Free</span>
            <span class="hidden md:flex items-center"><i class="fa-solid fa-droplet mr-2"></i> Pure Extracts</span>
            <span class="flex items-center"><i class="fa-solid fa-box mr-2"></i> Free Shipping Over $150</span>
        </div>
    </div>

    <!-- Main Shop Section -->
    <main id="shop" class="max-w-7xl mx-auto py-20 px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-12">
            <div>
                <h3 class="text-4xl font-serif mb-2">Our Curated Selection</h3>
                <p class="text-gray-500 font-light">Find the fragrance that speaks your language.</p>
            </div>
            
            <!-- Filter Pills (Visual only for now) -->
            <div class="flex space-x-2 mt-6 md:mt-0 overflow-x-auto no-scrollbar w-full md:w-auto pb-2">
                <a href="index.php" class="px-5 py-2 rounded-full border border-black bg-black text-white text-sm whitespace-nowrap">All</a>
                <a href="index.php?search=Woody" class="px-5 py-2 rounded-full border border-gray-300 hover:border-black text-sm whitespace-nowrap transition-colors">Woody</a>
                <a href="index.php?search=Floral" class="px-5 py-2 rounded-full border border-gray-300 hover:border-black text-sm whitespace-nowrap transition-colors">Floral</a>
                <a href="index.php?search=Fresh" class="px-5 py-2 rounded-full border border-gray-300 hover:border-black text-sm whitespace-nowrap transition-colors">Fresh</a>
            </div>
        </div>

        <?php if(empty($products)): ?>
            <div class="text-center py-20">
                <i class="fa-solid fa-flask text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-serif">No fragrances found.</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search criteria.</p>
                <a href="index.php" class="inline-block mt-6 border-b border-black pb-1 uppercase tracking-widest text-sm font-bold">Clear Search</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
                <?php foreach($products as $row): ?>
                <div class="group relative cursor-pointer flex flex-col">
                    <!-- Image Container -->
                    <div class="relative w-full aspect-[3/4] bg-gray-100 overflow-hidden mb-6">
                        <!-- Image Fallback if 'uploads/' is empty for testing -->
                        <?php $img_src = !empty($row['image']) ? "uploads/".$row['image'] : "https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=600&auto=format&fit=crop"; ?>
                        
                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" 
                             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out">
                        
                        <!-- Quick Add overlay -->
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
                            <!-- Form to handle adding to cart securely -->
                            <form action="cart.php" method="GET" class="w-11/12">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="w-full bg-white/95 backdrop-blur-sm text-black py-3 text-sm uppercase tracking-widest font-semibold hover:bg-black hover:text-white transition-colors shadow-lg">
                                    Quick Add
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="text-center flex-1 flex flex-col justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-[0.2em] mb-2"><?php echo htmlspecialchars($row['category'] ?? 'Signature'); ?></p>
                            <h4 class="font-serif text-lg leading-tight mb-2 group-hover:text-gray-600 transition-colors"><?php echo htmlspecialchars($row['name']); ?></h4>
                        </div>
                        <p class="text-sm font-medium mt-2">$<?php echo number_format($row['price'], 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Premium Footer -->
    <footer class="bg-brand text-white pt-20 pb-10 border-t border-gray-900 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                
                <div class="col-span-1 md:col-span-2">
                    <h2 class="text-3xl font-serif font-black uppercase tracking-tighter mb-6">Black Ivie</h2>
                    <p class="text-gray-400 font-light max-w-sm mb-6">Subscribe to receive updates, access to exclusive deals, and more.</p>
                    <form class="flex border-b border-gray-600 pb-2 max-w-md focus-within:border-white transition-colors">
                        <input type="email" placeholder="Enter your email address" class="w-full bg-transparent outline-none text-sm placeholder-gray-500">
                        <button type="submit" class="text-sm uppercase tracking-widest hover:text-gray-300">Subscribe</button>
                    </form>
                </div>

                <div>
                    <h4 class="text-xs uppercase tracking-widest font-semibold mb-6">Shop</h4>
                    <ul class="space-y-4 text-gray-400 font-light text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">All Perfumes</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Best Sellers</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Gift Sets</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Discovery Scent</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs uppercase tracking-widest font-semibold mb-6">Client Care</h4>
                    <ul class="space-y-4 text-gray-400 font-light text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Shipping & Returns</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-gray-800 text-xs text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> Black Ivie. All Rights Reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0 text-lg">
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
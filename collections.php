<?php
require 'config.php';

// 1. Fetch dynamically generated collections based on the 'category' column
$stmt = $pdo->query("
    SELECT 
        category, 
        COUNT(id) as total_products, 
        MIN(image) as sample_image 
    FROM products 
    GROUP BY category 
    HAVING category != ''
");
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Cart Items for Navbar
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Fallback high-end images if a category doesn't have a product image yet
$default_images = [
    'Floral' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=1200&auto=format&fit=crop',
    'Woody' => 'https://images.unsplash.com/photo-1605265125309-84b2eb2b0693?q=80&w=1200&auto=format&fit=crop',
    'Fresh' => 'https://images.unsplash.com/photo-1629198688000-71f23e745b6e?q=80&w=1200&auto=format&fit=crop',
    'Oriental' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=1200&auto=format&fit=crop'
];
$fallback_default = 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?q=80&w=1200&auto=format&fit=crop';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections | Black Ivie</title>
    
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
<body class="bg-stone text-brand font-sans antialiased" x-data="{ mobileMenuOpen: false, searchOpen: false }">

    <!-- Premium Navbar (Identical to index.php for consistency) -->
    <nav class="bg-stone/90 backdrop-blur-md sticky top-0 z-50 border-b border-gray-200/50 transition-all duration-300">
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
                    <a href="collections.php" class="text-sm tracking-widest uppercase text-gray-500 transition-colors">Collections</a>
                    <a href="about.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">About</a>
                </div>
                <div class="flex-shrink-0 flex justify-center w-1/3">
                    <a href="index.php" class="text-3xl font-black tracking-tighter uppercase font-serif">Black Ivie</a>
                </div>
                <div class="flex items-center justify-end space-x-6 w-1/3 hidden sm:flex">
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="text-brand hover:text-gray-500 transition-colors">
                        <i class="fa-regular fa-user text-lg"></i>
                    </a>
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
            <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="text-2xl font-serif border-b border-gray-100 pb-4">My Account</a>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="py-24 text-center px-4">
        <p class="text-xs uppercase tracking-[0.3em] text-gray-500 mb-4">Discover</p>
        <h1 class="text-5xl md:text-7xl font-serif italic mb-6">The Collections</h1>
        <p class="text-lg font-light text-gray-600 max-w-2xl mx-auto">Explore our olfactive families. Each collection is a masterclass in raw materials and emotional resonance.</p>
    </header>

    <!-- Main Collections Feed -->
    <main class="pb-24">
        <?php if(empty($collections)): ?>
            <!-- Empty State if no categories exist in DB -->
            <div class="text-center py-20">
                <i class="fa-solid fa-layer-group text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-serif">No collections curated yet.</h3>
                <p class="text-gray-500 mt-2">Check back soon for new arrivals.</p>
            </div>
        <?php else: ?>
            <div class="flex flex-col">
                <?php 
                $counter = 0;
                foreach($collections as $col): 
                    $category_name = htmlspecialchars($col['category']);
                    $item_count = $col['total_products'];
                    
                    // Determine image: Use DB product image, or fallback to our default array, or general default
                    $img = $fallback_default;
                    if (!empty($col['sample_image']) && file_exists('uploads/' . $col['sample_image'])) {
                        $img = 'uploads/' . $col['sample_image'];
                    } elseif (array_key_exists($category_name, $default_images)) {
                        $img = $default_images[$category_name];
                    }

                    // Alternate layout logic: even rows have text left, odd rows have text right
                    $is_even = ($counter % 2 == 0);
                ?>
                
                <!-- Single Collection Block -->
                <section class="flex flex-col <?php echo $is_even ? 'md:flex-row' : 'md:flex-row-reverse'; ?> group">
                    
                    <!-- Image Side -->
                    <div class="w-full md:w-1/2 h-[60vh] md:h-[80vh] overflow-hidden relative">
                        <img src="<?php echo $img; ?>" alt="<?php echo $category_name; ?> Collection" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000 ease-in-out filter grayscale hover:grayscale-0">
                        <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors duration-700"></div>
                    </div>
                    
                    <!-- Text Side -->
                    <div class="w-full md:w-1/2 flex items-center justify-center p-12 lg:p-24 bg-white/50 backdrop-blur-sm">
                        <div class="max-w-md <?php echo $is_even ? 'text-left' : 'text-left md:text-right'; ?>">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500 mb-4">0<?php echo $counter + 1; ?> &mdash; Olfactive Family</p>
                            <h2 class="text-4xl lg:text-6xl font-serif mb-6"><?php echo $category_name; ?></h2>
                            <p class="text-gray-500 font-light mb-8 leading-relaxed">
                                <?php 
                                // Dynamic descriptions based on category
                                if(strtolower($category_name) == 'woody') echo "Deep, earthy, and profoundly grounding. Experience notes of cedar, sandalwood, and vetiver.";
                                elseif(strtolower($category_name) == 'floral') echo "A modern interpretation of nature's blooms. Delicate, intoxicating, and eternally romantic.";
                                elseif(strtolower($category_name) == 'fresh') echo "Crisp, vibrant, and incredibly pure. Like the first breath of morning air by the coast.";
                                else echo "A unique curation of signature scents crafted for the uncompromising individual.";
                                ?>
                            </p>
                            
                            <a href="index.php?search=<?php echo urlencode($category_name); ?>" class="inline-flex items-center group/btn text-sm uppercase tracking-widest font-bold">
                                View <?php echo $item_count; ?> Fragrances 
                                <span class="block w-8 h-[1px] bg-black ml-4 group-hover/btn:w-16 transition-all duration-300"></span>
                            </a>
                        </div>
                    </div>
                </section>
                
                <?php 
                $counter++;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer (Identical to index.php) -->
    <footer class="bg-brand text-white pt-20 pb-10 border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center md:text-left">
            <h2 class="text-3xl font-serif uppercase tracking-tighter mb-4">Black Ivie</h2>
            <p class="text-gray-400 font-light text-sm">&copy; <?php echo date('Y'); ?> Black Ivie. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
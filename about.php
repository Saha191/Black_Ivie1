<?php
require 'config.php';

// Calculate Cart Items for Navbar
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story | Black Ivie</title>
    
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

    <!-- Premium Navbar -->
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
                    <a href="collections.php" class="text-sm tracking-widest uppercase hover:text-gray-500 transition-colors">Collections</a>
                    <a href="about.php" class="text-sm tracking-widest uppercase text-gray-500 transition-colors border-b border-black pb-1">About</a>
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
            <a href="about.php" class="text-2xl font-serif border-b border-gray-100 pb-4">About</a>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>" class="text-2xl font-serif border-b border-gray-100 pb-4">My Account</a>
        </div>
    </nav>

    <!-- Cinematic Hero Section -->
    <header class="relative h-[70vh] flex items-center justify-center overflow-hidden">
        <img src="https://images.unsplash.com/photo-1608528577891-eb05fcecb437?q=80&w=2000&auto=format&fit=crop" alt="Perfumery Art" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/30"></div>
        
        <div class="relative z-10 text-center px-4 text-white">
            <h1 class="text-5xl md:text-7xl font-serif italic mb-4">The Art of Scent.</h1>
            <p class="text-sm uppercase tracking-[0.3em] font-light">Redefining modern perfumery since 2026.</p>
        </div>
    </header>

    <main>
        <!-- The Philosophy Section -->
        <section class="max-w-7xl mx-auto py-24 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center gap-16 lg:gap-24">
                <div class="w-full md:w-1/2">
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-500 mb-4">Our Philosophy</p>
                    <h2 class="text-4xl md:text-5xl font-serif mb-8 leading-tight">Fragrance is the most powerful tie to memory.</h2>
                    <div class="space-y-6 text-gray-600 font-light leading-relaxed">
                        <p>At Black Ivie, we believe that a scent is more than just a beautifully engineered chemical composition; it is an invisible garment, a mood architect, and a quiet statement of identity.</p>
                        <p>Born out of a rebellion against mass-produced, synthetic-heavy perfumes, we set out to create a house that honors the traditional art of extraction while embracing avant-garde scent profiles.</p>
                        <p>Every bottle we craft is a testament to the uncompromising pursuit of raw, emotional resonance.</p>
                    </div>
                </div>
                <div class="w-full md:w-1/2 h-[600px] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1615525547053-9a1b140661ff?q=80&w=1200&auto=format&fit=crop" alt="Pouring perfume oils" class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-1000">
                </div>
            </div>
        </section>

        <!-- The Craftsmanship Grid -->
        <section class="bg-white py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-serif mb-4">Uncompromising Craft</h2>
                    <p class="text-gray-500 font-light max-w-2xl mx-auto">The pillars upon which every Black Ivie creation stands.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <!-- Craft Pillar 1 -->
                    <div class="p-6">
                        <i class="fa-solid fa-leaf text-3xl mb-6 text-gray-300"></i>
                        <h3 class="text-xs uppercase tracking-[0.2em] font-bold mb-4">Ethical Sourcing</h3>
                        <p class="text-gray-500 font-light text-sm leading-relaxed">From the vetiver fields of Haiti to the rose valleys of Grasse, we partner directly with farmers to ensure sustainable, cruelty-free harvesting of our raw absolutes.</p>
                    </div>
                    
                    <!-- Craft Pillar 2 -->
                    <div class="p-6">
                        <i class="fa-solid fa-flask-vial text-3xl mb-6 text-gray-300"></i>
                        <h3 class="text-xs uppercase tracking-[0.2em] font-bold mb-4">High Concentration</h3>
                        <p class="text-gray-500 font-light text-sm leading-relaxed">We bypass the fleeting nature of standard Eau de Toilettes. Our collections consist purely of Extrait de Parfum, ensuring a rich, complex sillage that lasts from dusk till dawn.</p>
                    </div>

                    <!-- Craft Pillar 3 -->
                    <div class="p-6">
                        <i class="fa-solid fa-genderless text-3xl mb-6 text-gray-300"></i>
                        <h3 class="text-xs uppercase tracking-[0.2em] font-bold mb-4">Genderless Expression</h3>
                        <p class="text-gray-500 font-light text-sm leading-relaxed">Scent has no gender. We formulate our fragrances to interact uniquely with the wearer's skin chemistry, creating a deeply personal and intimate olfactory signature.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quote/Founder Note -->
        <section class="bg-brand text-white py-32 px-4 text-center">
            <div class="max-w-3xl mx-auto">
                <i class="fa-solid fa-quote-left text-4xl text-gray-700 mb-8"></i>
                <blockquote class="text-3xl md:text-4xl font-serif italic leading-relaxed mb-8">
                    "We do not create perfumes to mask the wearer. We create them to reveal the wearer's deepest, most authentic self."
                </blockquote>
                <p class="text-xs uppercase tracking-[0.3em] font-bold text-gray-400">&mdash; The Founders, Black Ivie</p>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-24 text-center px-4 bg-stone">
            <h2 class="text-3xl font-serif mb-6">Begin Your Journey</h2>
            <p class="text-gray-500 font-light mb-10 max-w-md mx-auto">Explore our olfactive families and discover the scent that will become your signature.</p>
            <a href="collections.php" class="inline-block bg-brand text-white px-12 py-4 text-sm uppercase tracking-widest font-bold hover:bg-gray-800 transition-colors shadow-xl">
                Explore Collections
            </a>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-brand text-white pt-20 pb-10 border-t border-gray-900">
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
                        <li><a href="index.php" class="hover:text-white transition-colors">All Perfumes</a></li>
                        <li><a href="collections.php" class="hover:text-white transition-colors">Collections</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Gift Sets</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs uppercase tracking-widest font-semibold mb-6">Client Care</h4>
                    <ul class="space-y-4 text-gray-400 font-light text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Shipping & Returns</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-gray-800 text-xs text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> Black Ivie. All Rights Reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0 text-lg">
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
<?php
require 'config.php';

// 1. Strict Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// 2. Handle Add New Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $image_name = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('perfume_') . '.' . $file_extension;
        $target_file = $upload_dir . $image_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $error_msg = "Failed to upload image.";
        }
    }

    if (empty($error_msg)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, description, category, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $price, $stock, $description, $category, $image_name])) {
            $success_msg = "Product successfully added to catalog.";
        } else {
            $error_msg = "Database error. Could not add product.";
        }
    }
}

// 3. Handle Posting a Blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    $stmt = $pdo->prepare("INSERT INTO blogs (title, content, author_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $content, $_SESSION['user_id']])) {
        $success_msg = "Blog post published successfully.";
    } else {
        $error_msg = "Failed to publish blog post.";
    }
}

// 4. Handle Deletions
if (isset($_GET['delete_product'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_GET['delete_product']]);
    header("Location: admin.php"); exit;
}
if (isset($_GET['delete_blog'])) {
    $pdo->prepare("DELETE FROM blogs WHERE id = ?")->execute([(int)$_GET['delete_blog']]);
    header("Location: admin.php"); exit;
}

// 5. Fetch Dashboard Statistics & Data
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Pending'")->fetchColumn() ?: 0.00;
$total_orders = $pdo->query("SELECT COUNT(order_id) FROM orders")->fetchColumn() ?: 0;
$total_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn() ?: 0;
$total_buyers = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'buyer'")->fetchColumn() ?: 0;

$recent_orders = $pdo->query("SELECT o.*, u.fullname, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$all_products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$all_buyers = $pdo->query("SELECT id, fullname, email, created_at FROM users WHERE role = 'buyer' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$all_blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin HQ | Black Ivie</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
<body class="bg-stone text-brand font-sans antialiased h-screen overflow-hidden flex" x-data="{ tab: 'dashboard', sidebarOpen: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-transition.opacity x-cloak></div>

    <!-- Sidebar: The fix is adding "lg:translate-x-0" right here -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed lg:relative inset-y-0 left-0 w-64 flex-shrink-0 bg-brand text-white transition-transform duration-300 ease-in-out z-50 flex flex-col h-full shadow-2xl lg:shadow-none lg:translate-x-0">
        <div class="p-6 xl:p-8 border-b border-gray-800 flex justify-between items-center">
            <h1 class="text-2xl font-black tracking-tighter font-serif">BLACK IVIE</h1>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto no-scrollbar text-sm">
            <button @click="tab = 'dashboard'; sidebarOpen = false" :class="tab === 'dashboard' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-chart-pie w-5 mr-3"></i> Overview
            </button>
            <button @click="tab = 'add_product'; sidebarOpen = false" :class="tab === 'add_product' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-plus w-5 mr-3"></i> Add Product
            </button>
            <button @click="tab = 'catalog'; sidebarOpen = false" :class="tab === 'catalog' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-bottle-droplet w-5 mr-3"></i> Manage Catalog
            </button>
            <button @click="tab = 'orders'; sidebarOpen = false" :class="tab === 'orders' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-box-open w-5 mr-3"></i> Client Orders
            </button>
            <button @click="tab = 'buyers'; sidebarOpen = false" :class="tab === 'buyers' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-users w-5 mr-3"></i> Manage Buyers
            </button>
            <button @click="tab = 'blogs'; sidebarOpen = false" :class="tab === 'blogs' ? 'bg-white/10 text-white font-bold' : 'text-gray-400 hover:bg-white/5 hover:text-white'" class="w-full text-left px-4 py-3 rounded-lg flex items-center transition-all">
                <i class="fa-solid fa-pen-nib w-5 mr-3"></i> Journal / Blogs
            </button>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 px-4 py-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-white text-brand flex items-center justify-center font-bold text-xs flex-shrink-0">AD</div>
                <div class="text-sm truncate">
                    <p class="font-bold truncate"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                    <p class="text-gray-500 text-xs">Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="block w-full text-center py-2 text-sm text-red-400 hover:text-white hover:bg-red-500/20 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 h-full overflow-y-auto bg-stone">
        
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center lg:hidden sticky top-0 z-30">
            <button @click="sidebarOpen = true" class="text-brand hover:text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="font-serif font-black tracking-tighter text-lg">BLACK IVIE</h2>
        </header>

        <div class="p-6 lg:p-10 max-w-7xl mx-auto w-full pb-20">
            
            <?php if ($success_msg): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-8 flex items-center shadow-sm text-sm">
                    <i class="fa-solid fa-check-circle mr-3"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-8 flex items-center shadow-sm text-sm">
                    <i class="fa-solid fa-exclamation-circle mr-3"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- TAB: DASHBOARD OVERVIEW -->
            <div x-show="tab === 'dashboard'" x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">Overview</h2>
                    <p class="text-gray-500 text-sm">Welcome to the Black Ivie command center.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-10">
                    <div class="bg-white p-6 border border-gray-200 shadow-sm rounded-lg flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-4"><h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Revenue</h3><i class="fa-solid fa-vault text-gray-300 text-lg"></i></div>
                        <p class="text-2xl lg:text-3xl font-serif font-bold truncate">$<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                    <div class="bg-white p-6 border border-gray-200 shadow-sm rounded-lg flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-4"><h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Orders</h3><i class="fa-solid fa-receipt text-gray-300 text-lg"></i></div>
                        <p class="text-2xl lg:text-3xl font-serif font-bold"><?php echo $total_orders; ?></p>
                    </div>
                    <div class="bg-white p-6 border border-gray-200 shadow-sm rounded-lg flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-4"><h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Products</h3><i class="fa-solid fa-flask text-gray-300 text-lg"></i></div>
                        <p class="text-2xl lg:text-3xl font-serif font-bold"><?php echo $total_products; ?></p>
                    </div>
                    <div class="bg-white p-6 border border-gray-200 shadow-sm rounded-lg flex flex-col justify-between">
                        <div class="flex justify-between items-center mb-4"><h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Clients</h3><i class="fa-solid fa-users text-gray-300 text-lg"></i></div>
                        <p class="text-2xl lg:text-3xl font-serif font-bold"><?php echo $total_buyers; ?></p>
                    </div>
                </div>

                <!-- Snapshot Order Table -->
                <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-lg font-serif">Recent Transactions</h3>
                        <button @click="tab = 'orders'" class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black">View All</button>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-stone/50 text-xs uppercase tracking-widest text-gray-500">
                                    <th class="py-3 px-6 font-semibold">Order</th>
                                    <th class="py-3 px-6 font-semibold">Client</th>
                                    <th class="py-3 px-6 font-semibold">Status</th>
                                    <th class="py-3 px-6 font-semibold text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach(array_slice($recent_orders, 0, 5) as $order): ?>
                                <tr class="hover:bg-stone/30">
                                    <td class="py-4 px-6 text-sm font-medium">#BI-<?php echo str_pad($order['order_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td class="py-4 px-6 text-sm text-gray-600 truncate max-w-[150px]"><?php echo htmlspecialchars($order['fullname']); ?></td>
                                    <td class="py-4 px-6 text-sm">
                                        <span class="px-2 py-1 rounded-full text-[10px] uppercase tracking-widest font-bold <?php echo ($order['status'] == 'Delivered') ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-600'; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-semibold text-right">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: ADD PRODUCT -->
            <div x-show="tab === 'add_product'" x-cloak x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">New Fragrance</h2>
                    <p class="text-gray-500 text-sm">Add a new perfume to the Black Ivie catalog.</p>
                </div>

                <form action="admin.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 lg:p-8 border border-gray-200 shadow-sm rounded-lg max-w-4xl">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="col-span-1 md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Perfume Name</label>
                            <input type="text" name="name" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Retail Price ($)</label>
                            <input type="number" step="0.01" name="price" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Stock Level</label>
                            <input type="number" name="stock" value="10" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm">
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Olfactive Family</label>
                            <select name="category" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all bg-white text-sm">
                                <option value="Woody">Woody</option>
                                <option value="Floral">Floral</option>
                                <option value="Fresh">Fresh</option>
                                <option value="Oriental">Oriental</option>
                            </select>
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Description & Notes</label>
                            <textarea name="description" rows="4" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm"></textarea>
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Product Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-gray-100 file:text-black hover:file:bg-gray-200 cursor-pointer border border-gray-300 rounded-lg p-1">
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-3 pt-4 border-t border-gray-100">
                            <button type="submit" name="add_product" class="w-full md:w-auto px-8 bg-brand text-white font-bold py-3 text-sm rounded-lg uppercase tracking-widest hover:bg-gray-900 transition-all shadow-md">Publish Product</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB: MANAGE CATALOG -->
            <div x-show="tab === 'catalog'" x-cloak x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">The Catalog</h2>
                    <p class="text-gray-500 text-sm">Manage active collections and inventory.</p>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden w-full">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-stone/50 text-xs uppercase tracking-widest text-gray-500 border-b border-gray-200">
                                    <th class="py-4 px-6 font-semibold">Product</th>
                                    <th class="py-4 px-6 font-semibold text-center">Stock</th>
                                    <th class="py-4 px-6 font-semibold">Price</th>
                                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach($all_products as $prod): ?>
                                <tr class="hover:bg-stone/30">
                                    <td class="py-4 px-6 flex items-center gap-4">
                                        <div class="w-10 h-14 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                            <?php $img_src = !empty($prod['image']) ? "uploads/".$prod['image'] : "https://images.unsplash.com/photo-1594035910387-fea47794261f?w=100&q=80"; ?>
                                            <img src="<?php echo htmlspecialchars($img_src); ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div class="truncate">
                                            <span class="block font-serif font-bold text-base"><?php echo htmlspecialchars($prod['name']); ?></span>
                                            <span class="text-[10px] text-gray-400 uppercase tracking-widest"><?php echo htmlspecialchars($prod['category']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php 
                                            $stock_color = $prod['stock'] > 5 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'; 
                                            if($prod['stock'] == 0) $stock_color = 'text-gray-600 bg-gray-100';
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-[10px] uppercase tracking-widest font-bold <?php echo $stock_color; ?>"><?php echo $prod['stock'] == 0 ? 'Out of Stock' : $prod['stock'] . ' Units'; ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-semibold">$<?php echo number_format($prod['price'], 2); ?></td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="admin.php?delete_product=<?php echo $prod['id']; ?>" onclick="return confirm('Delete this product permanently?');" class="text-gray-400 hover:text-red-600"><i class="fa-regular fa-trash-can text-lg"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: ALL ORDERS -->
            <div x-show="tab === 'orders'" x-cloak x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">Client Orders</h2>
                    <p class="text-gray-500 text-sm">Comprehensive list of all transactions.</p>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-stone/50 text-xs uppercase tracking-widest text-gray-500 border-b border-gray-200">
                                    <th class="py-4 px-6 font-semibold">Order ID</th>
                                    <th class="py-4 px-6 font-semibold">Client Details</th>
                                    <th class="py-4 px-6 font-semibold">Date</th>
                                    <th class="py-4 px-6 font-semibold">Status</th>
                                    <th class="py-4 px-6 font-semibold text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach($recent_orders as $order): ?>
                                <tr class="hover:bg-stone/30">
                                    <td class="py-4 px-6 text-sm font-bold">#BI-<?php echo str_pad($order['order_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td class="py-4 px-6">
                                        <p class="font-semibold text-sm truncate max-w-[150px]"><?php echo htmlspecialchars($order['fullname']); ?></p>
                                        <p class="text-xs text-gray-400 truncate max-w-[150px]"><?php echo htmlspecialchars($order['email']); ?></p>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500 whitespace-nowrap"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="py-4 px-6 text-sm">
                                        <?php 
                                            $color = 'bg-gray-100 text-gray-600';
                                            if($order['status'] == 'Shipped') $color = 'bg-blue-50 text-blue-600';
                                            if($order['status'] == 'Delivered') $color = 'bg-green-50 text-green-600';
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-[10px] uppercase tracking-widest font-bold <?php echo $color; ?>"><?php echo $order['status']; ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-semibold text-right">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: MANAGE BUYERS -->
            <div x-show="tab === 'buyers'" x-cloak x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">Client Registry</h2>
                    <p class="text-gray-500 text-sm">Manage your registered customers.</p>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-stone/50 text-xs uppercase tracking-widest text-gray-500 border-b border-gray-200">
                                    <th class="py-4 px-6 font-semibold">Client Name</th>
                                    <th class="py-4 px-6 font-semibold">Email</th>
                                    <th class="py-4 px-6 font-semibold">Member Since</th>
                                    <th class="py-4 px-6 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach($all_buyers as $buyer): ?>
                                <tr class="hover:bg-stone/30">
                                    <td class="py-4 px-6 text-base font-bold font-serif whitespace-nowrap"><?php echo htmlspecialchars($buyer['fullname']); ?></td>
                                    <td class="py-4 px-6 text-sm text-gray-500"><?php echo htmlspecialchars($buyer['email']); ?></td>
                                    <td class="py-4 px-6 text-sm text-gray-500 whitespace-nowrap"><?php echo date('M j, Y', strtotime($buyer['created_at'])); ?></td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="mailto:<?php echo htmlspecialchars($buyer['email']); ?>" class="text-gray-400 hover:text-brand transition-colors"><i class="fa-regular fa-envelope text-lg"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: POST BLOGS -->
            <div x-show="tab === 'blogs'" x-cloak x-transition.opacity.duration.500ms>
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-serif">Publish Journal</h2>
                    <p class="text-gray-500 text-sm">Share scent profiles, brand news, and editorials.</p>
                </div>
                
                <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                    <!-- Form Side -->
                    <div class="w-full lg:w-1/2">
                        <form action="admin.php" method="POST" class="bg-white p-6 lg:p-8 border border-gray-200 shadow-sm rounded-lg">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Journal Title</label>
                                    <input type="text" name="title" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Editorial Content</label>
                                    <textarea name="content" rows="8" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black outline-none transition-all text-sm"></textarea>
                                </div>
                                <button type="submit" name="post_blog" class="w-full bg-brand text-white font-bold py-3 text-sm rounded-lg uppercase tracking-widest hover:bg-gray-900 transition-all shadow-md">Publish Entry</button>
                            </div>
                        </form>
                    </div>

                    <!-- List Side -->
                    <div class="w-full lg:w-1/2">
                        <h3 class="text-lg font-serif mb-4 font-bold">Recent Publications</h3>
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 no-scrollbar">
                            <?php if(empty($all_blogs)): ?>
                                <p class="text-gray-400 italic text-sm">No journals published yet.</p>
                            <?php else: ?>
                                <?php foreach($all_blogs as $blog): ?>
                                <div class="bg-white p-5 border border-gray-200 shadow-sm rounded-lg flex justify-between items-start">
                                    <div class="pr-4">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></p>
                                        <h4 class="font-serif font-bold text-base mb-2 leading-tight"><?php echo htmlspecialchars($blog['title']); ?></h4>
                                        <p class="text-xs text-gray-500 line-clamp-2"><?php echo htmlspecialchars($blog['content']); ?></p>
                                    </div>
                                    <a href="admin.php?delete_blog=<?php echo $blog['id']; ?>" onclick="return confirm('Delete this blog permanently?');" class="text-gray-300 hover:text-red-500 flex-shrink-0"><i class="fa-regular fa-trash-can"></i></a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</body>
</html>
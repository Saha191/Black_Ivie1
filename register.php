<?php 
require 'config.php'; 

// 1. Initialize CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Initialize variables for sticky form and errors
$errors = [];
$fullname_val = '';
$email_val = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    
    // 3. CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Security token validation failed. Please refresh the page.");
    }

    // 4. Sanitize Inputs
    $fullname = trim(filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    
    // Capture the selected role (default to buyer if tampered with)
    $role = $_POST['role'] ?? 'buyer';
    if (!in_array($role, ['admin', 'buyer'])) {
        $role = 'buyer'; 
    }

    // Keep values to repopulate the form
    $fullname_val = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
    $email_val = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    // 5. Server-Side Validation
    if (empty($fullname) || !preg_match("/^[a-zA-Z\s]{2,50}$/", $fullname)) {
        $errors[] = "Please enter a valid full name (letters and spaces only).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (strlen($password) < 8 || !preg_match("/[0-9]/", $password) || !preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must be at least 8 characters long, contain a number, and an uppercase letter.";
    }

    // 6. Check for Duplicate Email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        }
    }

    // 7. Insert User if No Errors
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            // Update the query to insert the dynamically selected $role
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $hash, $role]);
            
            $fullname_val = '';
            $email_val = '';
            $success_msg = "Account created successfully! Redirecting to login...";
            
            unset($_SESSION['csrf_token']);
            header("refresh:2;url=login.php");
        } catch (PDOException $e) {
            $errors[] = "A system error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Black Ivie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the animated floating label */
        .floating-input:focus-within label,
        .floating-input input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #000;
        }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans antialiased selection:bg-black selection:text-white min-h-screen flex">

    <!-- Left Side: Form Area -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24">
        <div class="w-full max-w-md">
            
            <div class="mb-10 text-center lg:text-left">
                <h1 class="text-4xl font-black tracking-tighter mb-2">BLACK IVIE</h1>
                <p class="text-gray-500">Join the exclusive world of luxury scents.</p>
            </div>

            <!-- Feedback Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="fa-solid fa-circle-exclamation text-red-500"></i></div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700">
                                <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success_msg): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 text-sm text-green-700 flex items-center">
                    <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="register.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Full Name Input -->
                <div class="relative floating-input bg-gray-50 rounded-lg px-4 pt-5 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors">
                    <input type="text" id="fullname" name="fullname" value="<?php echo $fullname_val; ?>" 
                           class="block w-full bg-transparent border-0 p-0 text-gray-900 placeholder-transparent focus:ring-0 sm:text-sm" 
                           placeholder="Full Name" required autocomplete="name">
                    <label for="fullname" class="absolute top-4 left-4 text-gray-400 text-sm transition-all duration-200 pointer-events-none origin-left">Full Name</label>
                </div>

                <!-- Email Input -->
                <div class="relative floating-input bg-gray-50 rounded-lg px-4 pt-5 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors">
                    <input type="email" id="email" name="email" value="<?php echo $email_val; ?>" 
                           class="block w-full bg-transparent border-0 p-0 text-gray-900 placeholder-transparent focus:ring-0 sm:text-sm" 
                           placeholder="Email" required autocomplete="email">
                    <label for="email" class="absolute top-4 left-4 text-gray-400 text-sm transition-all duration-200 pointer-events-none origin-left">Email Address</label>
                </div>

                <!-- Account Type Dropdown (NEW) -->
                <div class="relative bg-gray-50 rounded-lg px-4 pt-6 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors cursor-pointer">
                    <label for="role" class="absolute top-2 left-4 text-gray-400 text-[10px] uppercase font-bold tracking-widest transition-all duration-200 pointer-events-none">Account Type</label>
                    <select id="role" name="role" class="block w-full bg-transparent border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm appearance-none cursor-pointer outline-none">
                        <option value="buyer">Customer (Buyer)</option>
                        <option value="admin">Store Administrator</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="relative floating-input bg-gray-50 rounded-lg px-4 pt-5 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors">
                    <input type="password" id="password" name="password" 
                           class="block w-full bg-transparent border-0 p-0 text-gray-900 placeholder-transparent focus:ring-0 sm:text-sm pr-10" 
                           placeholder="Password" required>
                    <label for="password" class="absolute top-4 left-4 text-gray-400 text-sm transition-all duration-200 pointer-events-none origin-left">Password</label>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-black">
                        <i class="fa-regular fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                
                <!-- Password Requirements Hint -->
                <p class="text-xs text-gray-400 mt-1">Must be 8+ characters with an uppercase letter and a number.</p>

                <button type="submit" name="register" class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-black hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-transform active:scale-[0.98]">
                    Create Account <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                Already part of our world? 
                <a href="login.php" class="font-bold text-black hover:underline transition-all">Sign In</a>
            </p>
        </div>
    </div>

    <!-- Right Side: Luxury Hero Image -->
    <div class="hidden lg:block lg:w-1/2 relative bg-black">
        <img src="https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=2000&auto=format&fit=crop" 
             alt="Luxury Perfume Bottle" 
             class="absolute inset-0 w-full h-full object-cover opacity-80">
        
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 p-16 text-white max-w-xl">
            <h2 class="text-5xl font-serif mb-4 leading-tight">Your signature scent awaits.</h2>
            <p class="text-lg text-gray-300 font-light">Experience the craftsmanship and elegance of Black Ivie's exclusive collections.</p>
        </div>
    </div>

    <!-- JS for Password Visibility -->
    <script>
        function togglePassword() {
            const pwdInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
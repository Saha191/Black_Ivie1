<?php 
require 'config.php'; // Ensure session_start() is inside config.php

// 1. CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$email_value = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    // 2. CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Security token validation failed. Please refresh the page.");
    }

    // 3. Server-Side Input Sanitization
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    $email_value = htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address format.";
    } elseif (empty($password)) {
        $error = "Please enter your password.";
    } else {
        // 4. Secure Database Query
        $stmt = $pdo->prepare("SELECT id, fullname, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. Password Verification
        if ($user && password_verify($password, $user['password'])) {
            
            // 6. Prevent Session Fixation
            session_regenerate_id(true);

            // 7. Establish Secure Session Context
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['fullname'];
            $_SESSION['last_login'] = time(); 

            unset($_SESSION['csrf_token']);

            // Redirect based on role
            $redirect = ($user['role'] === 'admin') ? 'admin.php' : 'index.php';
            header("Location: " . $redirect);
            exit;
        } else {
            // 8. Prevent User Enumeration
            $error = "Invalid email or password.";
            usleep(500000); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Black Ivie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the animated floating label to match registration */
        .floating-input:focus-within label,
        .floating-input input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #000;
        }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans antialiased selection:bg-black selection:text-white min-h-screen flex">

    <!-- Left Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24">
        <div class="w-full max-w-md">
            
            <div class="mb-10 text-center lg:text-left">
                <h1 class="text-4xl font-black tracking-tighter mb-2">BLACK IVIE</h1>
                <p class="text-gray-500">Welcome back. Enter your details to access your curated collection.</p>
            </div>

            <!-- Error State -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6" role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0"><i class="fa-solid fa-circle-exclamation text-red-500"></i></div>
                        <div class="ml-3 text-sm text-red-700 font-medium">
                            <?php echo $error; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Email Input (Floating Label) -->
                <div class="relative floating-input bg-gray-50 rounded-lg px-4 pt-5 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors">
                    <input type="email" id="email" name="email" value="<?php echo $email_value; ?>" 
                           class="block w-full bg-transparent border-0 p-0 text-gray-900 placeholder-transparent focus:ring-0 sm:text-sm" 
                           placeholder="Email" required autocomplete="email">
                    <label for="email" class="absolute top-4 left-4 text-gray-400 text-sm transition-all duration-200 pointer-events-none origin-left">Email Address</label>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i class="fa-regular fa-envelope text-gray-400"></i>
                    </div>
                </div>

                <!-- Password Input (Floating Label + Toggle) -->
                <div class="relative floating-input bg-gray-50 rounded-lg px-4 pt-5 pb-2 border border-transparent focus-within:border-black focus-within:bg-white transition-colors">
                    <input type="password" id="password" name="password" 
                           class="block w-full bg-transparent border-0 p-0 text-gray-900 placeholder-transparent focus:ring-0 sm:text-sm pr-10" 
                           placeholder="Password" required autocomplete="current-password">
                    <label for="password" class="absolute top-4 left-4 text-gray-400 text-sm transition-all duration-200 pointer-events-none origin-left">Password</label>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-black transition-colors">
                        <i class="fa-regular fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded cursor-pointer transition-colors">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                            Remember me
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-bold text-gray-600 hover:text-black transition-colors">Forgot password?</a>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="login" class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-black hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-transform active:scale-[0.98]">
                    Sign In <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                New to Black Ivie? 
                <a href="register.php" class="font-bold text-black hover:underline transition-all">Create an account</a>
            </p>
        </div>
    </div>

    <!-- Right Side: Luxury Hero Image (Hidden on Mobile) -->
    <!-- Using a darker, moodier image to contrast with the registration page -->
    <div class="hidden lg:block lg:w-1/2 relative bg-black">
        <img src="https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?q=80&w=2000&auto=format&fit=crop" 
             alt="Dark Luxury Perfume" 
             class="absolute inset-0 w-full h-full object-cover opacity-70">
        
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 p-16 text-white max-w-xl">
            <h2 class="text-5xl font-serif mb-4 leading-tight">Elegance in every drop.</h2>
            <p class="text-lg text-gray-300 font-light">Sign in to explore your personalized recommendations and exclusive new arrivals.</p>
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
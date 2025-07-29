<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueingPro - Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-background">
            <div class="bg-circle circle-1"></div>
            <div class="bg-circle circle-2"></div>
            <div class="bg-circle circle-3"></div>
            <div class="bg-circle circle-4"></div>
            <div class="bg-circle circle-5"></div>
        </div>
        
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-users"></i>
                    <h1>QueueingPro</h1>
                </div>
                <p class="subtitle">Welcome Back</p>
                <p class="description">Sign in to access your admin dashboard</p>
            </div>
            
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        placeholder="Enter your username"
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" onclick="document.getElementById('password').type = document.getElementById('password').type === 'password' ? 'text' : 'password'; var icon = document.getElementById('toggleIcon'); if(document.getElementById('password').type === 'text') { icon.className = 'fas fa-eye-slash'; } else { icon.className = 'fas fa-eye'; }" tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <div class="divider">
                    <span>or</span>
                </div>
                
                <div class="quick-access">
                    <a href="index.php" class="quick-btn">
                        <i class="fas fa-desktop"></i>
                        Kiosk Mode
                    </a>
                    <a href="QueDisplay.php" class="quick-btn">
                        <i class="fas fa-tv"></i>
                        Display View
                    </a>
                </div>
            </form>
        </div>
        
        <div class="login-footer">
            <p>&copy; 2025 QueueingPro. All rights reserved.</p>
            <div class="footer-links">
                <a href="#"><i class="fas fa-shield-alt"></i> Security</a>
                <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                <a href="#"><i class="fas fa-phone"></i> Support</a>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>

</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueingPro - Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            
            <form class="login-form" id="loginForm" action="connections/auth.php" method="POST">
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
            
            <!-- Error/Success Messages -->
            <div class="message-container" id="messageContainer" style="display: none;">
                <div class="message" id="message"></div>
            </div>
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

    <script>
        // Simple password toggle (inline method should work as primary)
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput && toggleIcon) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.className = 'fas fa-eye-slash';
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.className = 'fas fa-eye';
                }
            }
        }

        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading state
        // Enhanced Form Interactions
        const form = document.getElementById('loginForm');
        const inputs = document.querySelectorAll('input');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        // Add floating label effect
        inputs.forEach(input => {
            // Input focus and blur effects
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
                addInputWave(this);
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
                removeInputWave(this);
            });

            // Input validation styling
            input.addEventListener('input', function() {
                validateField(this);
            });
        });

        function addInputWave(input) {
            const wave = document.createElement('div');
            wave.className = 'input-wave';
            wave.style.cssText = `
                position: absolute;
                bottom: 0;
                left: 50%;
                width: 0;
                height: 2px;
                background: linear-gradient(90deg, var(--accent-color), #5ba3f5);
                transform: translateX(-50%);
                transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 1px;
                z-index: 5;
            `;
            input.parentElement.appendChild(wave);
            
            setTimeout(() => {
                wave.style.width = '100%';
            }, 10);
        }

        function removeInputWave(input) {
            const wave = input.parentElement.querySelector('.input-wave');
            if (wave) {
                wave.style.width = '0';
                setTimeout(() => {
                    wave.remove();
                }, 400);
            }
        }

        function validateField(field) {
            const isValid = field.checkValidity();
            const formGroup = field.parentElement;
            
            if (field.value) {
                formGroup.classList.add('has-value');
                if (isValid) {
                    formGroup.classList.add('valid');
                    formGroup.classList.remove('invalid');
                } else {
                    formGroup.classList.add('invalid');
                    formGroup.classList.remove('valid');
                }
            } else {
                formGroup.classList.remove('has-value', 'valid', 'invalid');
            }
        }

        // Enhanced form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Add submission animation
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin btn-icon"></i><span class="btn-text">Signing In...</span>';
            submitBtn.disabled = true;
            submitBtn.style.transform = 'scale(0.98)';
            
            // Add loading state to form
            form.classList.add('submitting');
            
            // Simulate login process (replace with actual authentication)
            setTimeout(() => {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                submitBtn.style.transform = '';
                form.classList.remove('submitting');
                
                // For demo purposes - replace with actual login logic
                if (username === 'admin' && password === 'admin') {
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin/index.php';
                    }, 1500);
                } else {
                    showMessage('Invalid username or password', 'error');
                    // Add shake animation to form
                    form.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        form.style.animation = '';
                    }, 500);
                }
            }, 2000);
        });

        function showMessage(text, type) {
            const container = document.getElementById('messageContainer');
            const message = document.getElementById('message');
            
            message.textContent = text;
            message.className = `message ${type}`;
            container.style.display = 'block';
            
            // Add slide-in animation
            container.style.transform = 'translateY(-10px)';
            container.style.opacity = '0';
            
            setTimeout(() => {
                container.style.transform = 'translateY(0)';
                container.style.opacity = '1';
            }, 10);
            
            setTimeout(() => {
                container.style.transform = 'translateY(-10px)';
                container.style.opacity = '0';
                setTimeout(() => {
                    container.style.display = 'none';
                }, 300);
            }, 5000);
        }

        // Enhanced loading and focus effects
        window.addEventListener('load', () => {
            document.getElementById('username').focus();
            
            // Add entrance animations to elements
            const elements = document.querySelectorAll('.login-card > *');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${0.1 * index}s`;
            });
        });

        // Enhanced keyboard navigation
        document.querySelectorAll('input').forEach((input, index, inputs) => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const nextInput = inputs[index + 1];
                    if (nextInput) {
                        nextInput.focus();
                    } else {
                        form.dispatchEvent(new Event('submit'));
                    }
                }
            });
        });

        // Add CSS for shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            
            .form-group.focused label {
                color: var(--accent-color);
                transform: translateY(-2px);
            }
            
            .form-group.valid input {
                border-color: var(--success-color);
                box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.1);
            }
            
            .form-group.invalid input {
                border-color: var(--danger-color);
                box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
                animation: inputShake 0.3s ease-in-out;
            }
            
            @keyframes inputShake {
                0%, 100% { transform: translateX(0); }
                25%, 75% { transform: translateX(-3px); }
                50% { transform: translateX(3px); }
            }
            
            .login-form.submitting {
                opacity: 0.8;
                pointer-events: none;
            }
            
            #messageContainer {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

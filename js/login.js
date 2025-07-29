// Form submission handling with SweetAlert2 and database authentication
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const inputs = document.querySelectorAll('input');
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            // Form submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                const remember = document.getElementById('remember').checked;
                
                // Validation with SweetAlert2
                if (!username) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Username Required',
                        text: 'Please enter your username',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                    document.getElementById('username').focus();
                    return;
                }
                
                if (!password) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Password Required',
                        text: 'Please enter your password',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                    document.getElementById('password').focus();
                    return;
                }
                
                // Show loading with SweetAlert2
                Swal.fire({
                    title: 'Signing In...',
                    text: 'Please wait while we verify your credentials',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Make AJAX request to authenticate
                try {
                    const formData = new FormData();
                    formData.append('username', username);
                    formData.append('password', password);
                    formData.append('remember_me', remember ? '1' : '0');
                    formData.append('action', 'login');
                    
                    const response = await fetch('connections/auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            text: 'Welcome back, ' + result.user.Username + '!',
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                        
                        // Redirect based on user level
                        if (result.user.User_Lvl == 0) {
                            window.location.href = 'admin/index.php';
                        } else if (result.user.User_Lvl == 1) {
                            window.location.href = 'controller/index.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    } else {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: result.message || 'Invalid username or password. Please try again.',
                            // text: result.message || 'Invalid username or password. Please try again.',
                            confirmButtonText: 'Try Again',
                            confirmButtonColor: '#d33',
                            footer: 'Make sure your credentials are correct'
                        });
                        
                        // Clear password field and focus username
                        document.getElementById('password').value = '';
                        document.getElementById('username').focus();
                        document.getElementById('username').select();
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to connect to the server. Please try again.',
                        confirmButtonText: 'Retry',
                        confirmButtonColor: '#d33'
                    });
                }
            });

            // Input focus and blur effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });

                input.addEventListener('input', function() {
                    if (this.value) {
                        this.parentElement.classList.add('has-value');
                    } else {
                        this.parentElement.classList.remove('has-value');
                    }
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
                            loginForm.dispatchEvent(new Event('submit'));
                        }
                    }
                });
            });

            // Global Enter key login functionality
            document.addEventListener('keydown', function(e) {
                // Check if Enter key is pressed and form is visible
                if (e.key === 'Enter' && !e.target.matches('button')) {
                    // If no input is focused, focus username field
                    if (!document.activeElement.matches('input')) {
                        document.getElementById('username').focus();
                    }
                    // If an input is focused, proceed with form submission logic
                    else if (document.activeElement.matches('input')) {
                        const activeInput = document.activeElement;
                        const allInputs = Array.from(document.querySelectorAll('input[type="text"], input[type="password"]'));
                        const currentIndex = allInputs.indexOf(activeInput);
                        
                        if (currentIndex < allInputs.length - 1) {
                            // Move to next input
                            allInputs[currentIndex + 1].focus();
                        } else {
                            // Submit form if on last input
                            loginForm.dispatchEvent(new Event('submit'));
                        }
                    }
                }
            });

            // Check for remembered credentials on page load
            window.addEventListener('load', () => {
                // Check for logout success message
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('logout') === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Logged Out Successfully',
                        text: 'You have been safely logged out.',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                    
                    // Clean up URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                }

                // Check if credentials are remembered
                const rememberedUsername = getCookie('username');
                const rememberedPassword = getCookie('password');
                
                if (rememberedUsername && rememberedPassword) {
                    document.getElementById('username').value = rememberedUsername;
                    document.getElementById('password').value = rememberedPassword;
                    document.getElementById('remember').checked = true;
                    
                    // Add has-value class for styling
                    document.getElementById('username').parentElement.classList.add('has-value');
                    document.getElementById('password').parentElement.classList.add('has-value');
                }
                
                document.getElementById('username').focus();
            });

            // Forgot password with SweetAlert2
            const forgotPasswordLink = document.querySelector('.forgot-password');
            if (forgotPasswordLink) {
                forgotPasswordLink.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    await Swal.fire({
                        icon: 'info',
                        title: 'Password Recovery',
                        text: 'Please contact your system administrator for password recovery assistance.',
                        confirmButtonText: 'Contact Admin',
                        confirmButtonColor: '#3085d6',
                        footer: 'Email: admin@queueingpro.com | Phone: (555) 123-4567'
                    });
                });
            }
        });

        // Add custom CSS for enhanced styling
        const style = document.createElement('style');
        style.textContent = `
            .form-group.focused label {
                color: var(--accent-color);
                transform: translateY(-2px);
            }
            
            .form-group.has-value input {
                border-color: #4CAF50;
            }
            
            .swal2-popup {
                font-family: 'Poppins', sans-serif !important;
            }
            
            .swal2-title {
                font-weight: 600 !important;
            }
        `;
        document.head.appendChild(style);

        // Helper function to get cookie value
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
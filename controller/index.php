<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueingPro - Counter Controller</title>
    <link rel="stylesheet" href="css/controller.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="controller-container">
        <!-- Header -->
        <header class="controller-header">
            <div class="logo-section">
                <i class="fas fa-users"></i>
                <h1>QueueingPro</h1>
                <span class="subtitle">Counter Controller</span>
            </div>
            <div class="counter-info">
                <div class="user-info">
                    <label>
                        <i class="fas fa-user"></i>
                        Operator
                    </label>
                    <div class="user-name">
                        <span id="operatorName">John Doe</span>
                    </div>
                </div>
                <div class="counter-name">
                    <label for="counterSelect">
                        <i class="fas fa-desktop"></i>
                        Counter Name
                    </label>
                    <select id="counterSelect" class="counter-select">
                        <option value="1">Counter 1 - General Inquiry</option>
                        <option value="2">Counter 2 - Accounts</option>
                        <option value="3">Counter 3 - Loans</option>
                        <option value="4">Counter 4 - Cards</option>
                        <option value="5">Counter 5 - Deposits</option>
                        <option value="6">Counter 6 - Withdrawals</option>
                    </select>
                </div>
                <div class="status-indicator">
                    <span class="status active">
                        <i class="fas fa-circle"></i>
                        Active
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Controller Content -->
        <main class="controller-main">
            <!-- Queue Numbers Section -->
            <section class="queue-numbers">
                <div class="number-card previous">
                    <div class="card-header">
                        <i class="fas fa-arrow-left"></i>
                        <h3>Previous Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="previousNumber">A024</span>
                        <span class="status-text">Completed</span>
                    </div>
                </div>

                <div class="number-card current">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h3>Current Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="currentNumber">A025</span>
                        <span class="status-text">Serving</span>
                    </div>
                </div>

                <div class="number-card upcoming">
                    <div class="card-header">
                        <i class="fas fa-arrow-right"></i>
                        <h3>Next Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="upcomingNumber">A026</span>
                        <span class="status-text">Waiting</span>
                    </div>
                </div>
            </section>

            <!-- Manual Set Current Number -->
            <section class="manual-control">
                <div class="control-card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i>
                        <h3>Set Current Number</h3>
                    </div>
                    <div class="manual-input">
                        <label for="manualNumber">Enter Queue Number:</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="manualNumber" 
                                placeholder="e.g., A025"
                                maxlength="10"
                            >
                            <button type="button" class="btn-set" id="setNumberBtn">
                                <i class="fas fa-check"></i>
                                Set Number
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Action Buttons -->
            <section class="action-buttons">
                <button class="action-btn next-btn" id="nextNumberBtn">
                    <i class="fas fa-forward"></i>
                    <span>Next Number</span>
                    <small>Call next customer</small>
                </button>

                <button class="action-btn repeat-btn" id="repeatNumberBtn">
                    <i class="fas fa-redo"></i>
                    <span>Repeat Number</span>
                    <small>Call current again</small>
                </button>

                <button class="action-btn break-btn" id="breakBtn">
                    <i class="fas fa-pause"></i>
                    <span>Go On Break</span>
                    <small>Pause counter</small>
                </button>
            </section>

            <!-- Awaiting Queue -->
            <section class="awaiting-queue">
                <div class="queue-header">
                    <h3>
                        <i class="fas fa-clock"></i>
                        Awaiting Queue
                    </h3>
                    <span class="queue-count">12 customers waiting</span>
                </div>
                <div class="queue-list" id="awaitingList">
                    <div class="queue-item">
                        <span class="queue-number">A026</span>
                        <span class="service-type">General Inquiry</span>
                        <span class="wait-time">5 min</span>
                    </div>
                    <div class="queue-item">
                        <span class="queue-number">A027</span>
                        <span class="service-type">Account Opening</span>
                        <span class="wait-time">8 min</span>
                    </div>
                    <div class="queue-item">
                        <span class="queue-number">A028</span>
                        <span class="service-type">Loan Application</span>
                        <span class="wait-time">12 min</span>
                    </div>
                    <div class="queue-item">
                        <span class="queue-number">A029</span>
                        <span class="service-type">Card Services</span>
                        <span class="wait-time">15 min</span>
                    </div>
                    <div class="queue-item">
                        <span class="queue-number">A030</span>
                        <span class="service-type">Deposits</span>
                        <span class="wait-time">18 min</span>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer Actions -->
        <footer class="controller-footer">
            <div class="footer-actions">
                <a href="../QueDisplay.php" class="footer-btn">
                    <i class="fas fa-tv"></i>
                    Display View
                </a>
                <a href="../login.php" class="footer-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </footer>
    </div>

    <!-- Success/Error Messages -->
    <div class="message-container" id="messageContainer" style="display: none;">
        <div class="message" id="message"></div>
    </div>

    <script>
        // Controller functionality
        class QueueController {
            constructor() {
                this.initializeEventListeners();
                this.currentNumber = 'A025';
                this.previousNumber = 'A024';
                this.upcomingNumber = 'A026';
                this.isOnBreak = false;
                this.operatorName = 'John Doe'; // Static operator name
                this.updateDisplay();
            }

            initializeEventListeners() {
                // Next Number button
                document.getElementById('nextNumberBtn').addEventListener('click', () => {
                    this.nextNumber();
                });

                // Repeat Number button
                document.getElementById('repeatNumberBtn').addEventListener('click', () => {
                    this.repeatNumber();
                });

                // Break button
                document.getElementById('breakBtn').addEventListener('click', () => {
                    this.toggleBreak();
                });

                // Set Number button
                document.getElementById('setNumberBtn').addEventListener('click', () => {
                    this.setCurrentNumber();
                });

                // Counter selection
                document.getElementById('counterSelect').addEventListener('change', (e) => {
                    this.switchCounter(e.target.value);
                });

                // Enter key for manual number input
                document.getElementById('manualNumber').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.setCurrentNumber();
                    }
                });
            }

            getUserFullName() {
                return this.operatorName;
            }

            nextNumber() {
                if (this.isOnBreak) {
                    this.showMessage('Cannot call next number while on break', 'error');
                    return;
                }

                const operatorName = this.getUserFullName();
                this.previousNumber = this.currentNumber;
                this.currentNumber = this.upcomingNumber;
                this.upcomingNumber = this.generateNextNumber(this.upcomingNumber);
                
                this.updateDisplay();
                this.showMessage(`${operatorName} called number ${this.currentNumber}`, 'success');
                
                // Add animation to current number
                this.animateNumberChange('currentNumber');
            }

            repeatNumber() {
                if (this.isOnBreak) {
                    this.showMessage('Cannot repeat number while on break', 'error');
                    return;
                }

                const operatorName = this.getUserFullName();
                this.showMessage(`${operatorName} repeated number ${this.currentNumber}`, 'info');
                this.animateNumberChange('currentNumber');
            }

            toggleBreak() {
                const operatorName = this.getUserFullName();
                this.isOnBreak = !this.isOnBreak;
                const breakBtn = document.getElementById('breakBtn');
                const statusIndicator = document.querySelector('.status-indicator .status');
                
                if (this.isOnBreak) {
                    breakBtn.innerHTML = '<i class="fas fa-play"></i><span>Resume Service</span><small>Resume counter</small>';
                    breakBtn.classList.add('resume');
                    statusIndicator.innerHTML = '<i class="fas fa-pause"></i> On Break';
                    statusIndicator.classList.remove('active');
                    statusIndicator.classList.add('break');
                    this.showMessage(`${operatorName} is now on break`, 'warning');
                } else {
                    breakBtn.innerHTML = '<i class="fas fa-pause"></i><span>Go On Break</span><small>Pause counter</small>';
                    breakBtn.classList.remove('resume');
                    statusIndicator.innerHTML = '<i class="fas fa-circle"></i> Active';
                    statusIndicator.classList.remove('break');
                    statusIndicator.classList.add('active');
                    this.showMessage(`${operatorName} resumed service`, 'success');
                }
            }

            setCurrentNumber() {
                const input = document.getElementById('manualNumber');
                const newNumber = input.value.trim().toUpperCase();
                
                if (!newNumber) {
                    this.showMessage('Please enter a valid number', 'error');
                    return;
                }

                const operatorName = this.getUserFullName();
                this.previousNumber = this.currentNumber;
                this.currentNumber = newNumber;
                this.upcomingNumber = this.generateNextNumber(newNumber);
                
                this.updateDisplay();
                this.showMessage(`${operatorName} set current number to ${newNumber}`, 'success');
                input.value = '';
                
                this.animateNumberChange('currentNumber');
            }

            switchCounter(counterId) {
                const counterName = document.querySelector(`option[value="${counterId}"]`).textContent;
                const operatorName = this.getUserFullName();
                this.showMessage(`${operatorName} switched to ${counterName}`, 'info');
            }

            generateNextNumber(currentNum) {
                // Simple number generation logic
                const letter = currentNum.charAt(0);
                const number = parseInt(currentNum.slice(1)) + 1;
                return letter + number.toString().padStart(3, '0');
            }

            updateDisplay() {
                document.getElementById('previousNumber').textContent = this.previousNumber;
                document.getElementById('currentNumber').textContent = this.currentNumber;
                document.getElementById('upcomingNumber').textContent = this.upcomingNumber;
            }

            animateNumberChange(elementId) {
                const element = document.getElementById(elementId);
                element.style.transform = 'scale(1.1)';
                element.style.color = '#4A90E2';
                
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                    element.style.color = '';
                }, 300);
            }

            showMessage(text, type) {
                const container = document.getElementById('messageContainer');
                const message = document.getElementById('message');
                
                message.textContent = text;
                message.className = `message ${type}`;
                container.style.display = 'block';
                
                setTimeout(() => {
                    container.style.display = 'none';
                }, 3000);
            }
        }

        // Initialize controller when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new QueueController();
        });

        // Auto-refresh awaiting queue every 30 seconds
        setInterval(() => {
            // In a real application, this would fetch updated data from the server
            console.log('Refreshing queue data...');
        }, 30000);
    </script>
</body>
</html>

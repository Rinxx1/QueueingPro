<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue System - Select Transaction</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="kiosk-body">
    <!-- Main Kiosk Interface -->
    <main class="kiosk-main">
        <div class="kiosk-container">
            <!-- Transaction Selection -->
            <section class="transaction-section">
                <div class="kiosk-header">
                    <h1>Select Your Transaction</h1>
                    <p>Touch a service below to get your queue number</p>
                </div>
                <div class="transaction-grid">
                    <!-- General Banking -->
                    <div class="transaction-card" data-category="general">
                        <div class="card-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="card-content">
                            <h4>General Banking</h4>
                            <p>Account inquiries, statements, general assistance</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>5-10 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deposits & Withdrawals -->
                    <div class="transaction-card" data-category="deposit">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-content">
                            <h4>Deposits & Withdrawals</h4>
                            <p>Cash deposits, withdrawals, money transfers</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>3-7 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loans & Credit -->
                    <div class="transaction-card" data-category="loans">
                        <div class="card-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="card-content">
                            <h4>Loans & Credit</h4>
                            <p>Loan applications, credit cards, mortgage</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>15-25 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Services -->
                    <div class="transaction-card" data-category="account">
                        <div class="card-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="card-content">
                            <h4>Account Services</h4>
                            <p>Account opening, closures, modifications</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>10-20 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Business Banking -->
                    <div class="transaction-card" data-category="business">
                        <div class="card-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="card-content">
                            <h4>Business Banking</h4>
                            <p>Business accounts, merchant services</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>12-20 min</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Support -->
                    <div class="transaction-card" data-category="support">
                        <div class="card-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="card-content">
                            <h4>Customer Support</h4>
                            <p>Complaints, feedback, technical support</p>
                            <div class="estimated-time">
                                <i class="fas fa-clock"></i>
                                <span>8-15 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content confirmation-content">
            <div class="modal-header">
                <h3>Confirm Your Service</h3>
                <button class="close-btn" id="closeConfirmation">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-details">
                    <div class="confirmation-message" id="confirmationStep">
                        <div class="confirmation-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>Ready to proceed?</h4>
                        <p>You're about to join the queue for this service.</p>
                        <div class="process-info">
                            <div class="process-step">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Get your queue number</span>
                            </div>
                            <div class="process-step">
                                <i class="fas fa-clock"></i>
                                <span>Wait for your turn</span>
                            </div>
                            <div class="process-step">
                                <i class="fas fa-user-check"></i>
                                <span>Get served when called</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Final Step - Queue Number Generated -->
                    <div class="queue-number-step" id="queueNumberStep" style="display: none;">
                        <div class="service-details">
                            <div class="service-icon" id="confirmServiceIcon">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4 id="confirmServiceName">General Banking</h4>
                        </div>
                        <div class="generated-ticket">
                            <div class="ticket-number-display">
                                <span class="number-label">Your Queue Number</span>
                                <span class="generated-number" id="generatedNumber">A001</span>
                            </div>
                            <div class="ticket-details">
                                <p><strong>Service:</strong> <span id="finalServiceName">General Banking</span></p>
                                <p><strong>Date & Time:</strong> <span id="finalDateTime"></span></p>
                            </div>
                            <div class="final-message">
                                <p><strong>Please print your ticket and wait for your number to be called.</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <!-- Step 1 Actions -->
                <div id="confirmationActions">
                    <button class="btn-cancel" id="cancelService">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button class="btn-confirm" id="confirmService">
                        <i class="fas fa-check"></i>
                        Get Queue Number
                    </button>
                </div>
                
                <!-- Step 2 Actions -->
                <div id="finalActions" style="display: none;">
                    <button class="btn-print-final" id="printQueueNumber">
                        <i class="fas fa-print"></i>
                        Print Queue Number
                    </button>
                    <button class="btn-done" id="finishProcess">
                        <i class="fas fa-check-circle"></i>
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Modal -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Your Queue Ticket</h3>
                <button class="close-btn" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="ticket">
                    <div class="ticket-header">
                        <div class="logo-small">
                            <i class="fas fa-users"></i>
                            <span>QueueingPro</span>
                        </div>
                        <div class="ticket-date" id="ticketDate"></div>
                    </div>
                    <div class="ticket-number">
                        <span class="number-label">Your Number</span>
                        <span class="number" id="ticketNumber">A001</span>
                    </div>
                    <div class="ticket-info">
                        <div class="info-row">
                            <span class="label">Service:</span>
                            <span class="value" id="ticketService">General Banking</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Estimated Wait:</span>
                            <span class="value" id="ticketWait">5-10 minutes</span>
                        </div>
                        <div class="info-row">
                            <span class="label">People Ahead:</span>
                            <span class="value" id="ticketAhead">3</span>
                        </div>
                    </div>
                    <div class="ticket-footer">
                        <p>Please keep this ticket and wait for your number to be called</p>
                        <div class="qr-code">
                            <i class="fas fa-qrcode"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-print" id="printTicket">
                    <i class="fas fa-print"></i>
                    Print Ticket
                </button>
                <button class="btn-photo" id="takePhoto">
                    <i class="fas fa-camera"></i>
                    Take Photo
                </button>
                <button class="btn-close" id="closeTicket">
                    <i class="fas fa-check"></i>
                    Done
                </button>
            </div>
        </div>
    </div>

    <script src="js/counters.js"></script>
</body>
</html>
<?php
require_once 'connections/database.php';

// Get all active counters from the database
$counters = [];
try {
    $stmt = $pdo->prepare("
        SELECT Counter_ID, Counter_Name, Counter_Description, Counter_Status 
        FROM counters 
        WHERE Counter_Status = 'Active'
        ORDER BY Counter_Name ASC
    ");
    $stmt->execute();
    $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $counters = [];
}
?>
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
                    <?php if (empty($counters)): ?>
                        <div class="no-counters">
                            <div class="no-counters-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <h3>No Active Counters</h3>
                            <p>All service counters are currently offline. Please try again later.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($counters as $counter): ?>
                            <div class="transaction-card" data-category="counter" data-counter-id="<?php echo $counter['Counter_ID']; ?>">
                                <div class="card-icon">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="card-content">
                                    <h4><?php echo htmlspecialchars($counter['Counter_Name']); ?></h4>
                                    <p><?php echo htmlspecialchars($counter['Counter_Description'] ?: 'General service counter'); ?></p>
                                    <div class="estimated-time">
                                        <i class="fas fa-clock"></i>
                                        <span>5-10 min</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                            <div class="queue-info-display">
                                <!-- Queue info will be populated by JavaScript -->
                            </div>
                            <div class="ticket-details">
                                <p><strong>Service:</strong> <span id="finalServiceName">General Banking</span></p>
                                <p><strong>Date & Time:</strong> <span id="finalDateTime"></span></p>
                            </div>
                            <div class="final-message">
                                <p><strong>Please click proceed to finish the process.</strong></p>
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
                    <button class="btn-done" id="printQueueNumber">
                        <i class="fas fa-print"></i>
                        Proceed
                    </button>
                 <button class="btn-exit" id="exit">
                        <i class="fas fa-check-circle"></i>
                       Close
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
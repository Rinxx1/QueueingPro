<?php
session_start();
require_once '../connections/auth.php';
require_once '../connections/database.php';
require_once 'functions.php';

// Check if user is logged in and is controller
if (!isLoggedIn() || !isController()) {
    header("Location: ../login.php");
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$counterId = $_SESSION['counter_id'];

// Get user's full name and counter information
try {
    $stmt = $pdo->prepare("
        SELECT u.Firstname, u.Lastname, u.Username, c.Counter_Name, c.Counter_CurrentNumber, c.Counter_Status, c.Start_Time
        FROM users u
        LEFT JOIN counters c ON u.Counter_ID = c.Counter_ID
        WHERE u.User_ID = ?
    ");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        header("Location: ../login.php");
        exit();
    }
    
    $operatorName = $userInfo['Firstname'] . ' ' . $userInfo['Lastname'];
    $counterName = $userInfo['Counter_Name'] ?? 'Not Assigned';
    $currentNumber = $userInfo['Counter_CurrentNumber'] ?? 'A001';
    $counterStatus = $userInfo['Counter_Status'] ?? 'Active';
    $startTime = $userInfo['Start_Time'] ?? null;
    
    // Get awaiting queue for this counter
    $awaitingQueue = [];
    $nextAwaitingNumber = null;
    $lastCompletedNumber = null;
    if ($counterId) {
        $awaitingQueue = getAwaitingQueueForCounter($counterId);
        $nextAwaitingNumber = getNextAwaitingNumber($counterId);
        $lastCompletedNumber = getLastCompletedNumber($counterId);
    }
    
} catch(PDOException $e) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueingPro - Counter Controller</title>
    <link rel="stylesheet" href="css/controller.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="scripts.js?v=<?php echo time(); ?>" defer></script>

</head>
<body>
    <div class="controller-container">
        <!-- Header -->
        <header class="controller-header">
            <div class="logo-section">
                <!-- <i class="fas fa-users"></i> -->
                <h1>QueueingPro</h1>
                <span class="subtitle">Counter Controller</span>
            </div>
            <div class="counter-info">
                <!-- Operator Information Card -->
                <div class="user-info">
                    <div class="info-card operator-card">
                        <div class="info-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">
                                <i class="fas fa-id-badge"></i>
                                <span>Operator</span>
                            </div>
                            <div class="info-value">
                                <span id="operatorName"><?php echo htmlspecialchars($operatorName); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Counter Information Card -->
                <div class="counter-name">
                    <div class="info-card counter-card <?php echo ($counterName === 'Not Assigned') ? 'not-assigned' : 'assigned'; ?>">
                        <div class="info-icon">
                            <i class="fas fa-<?php echo ($counterName === 'Not Assigned') ? 'exclamation-triangle' : 'desktop'; ?>"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Counter</span>
                            </div>
                            <div class="info-value">
                                <span id="counterName" class="<?php echo ($counterName === 'Not Assigned') ? 'not-assigned-text' : ''; ?>">
                                    <?php echo htmlspecialchars($counterName); ?>
                                </span>
                                <?php if ($counterName === 'Not Assigned'): ?>
                                    <br>
                                <div class="assignment-badge">
                                    <i class="fas fa-warning"></i>
                                    Needs Assignment
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Information -->
                <div class="status-indicator">
                    <span class="status <?php echo strtolower($counterStatus); ?>">
                        <i class="fas <?php echo ($counterStatus === 'Active') ? 'fa-circle' : 'fa-pause-circle'; ?>"></i>
                        <?php echo htmlspecialchars($counterStatus); ?>
                    </span>
                </div>

            </div>
        </header>

        <!-- Main Controller Content -->
        <main class="controller-main">
            <?php if (!$counterId || $counterName === 'Not Assigned'): ?>
            <!-- Counter Assignment Required Section -->
            <section class="counter-assignment-required">
                <div class="assignment-card">
                    <div class="assignment-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2>Counter Assignment Required</h2>
                    </div>
                    <div class="assignment-content">
                        <div>
                            <p>Hello <strong><?php echo htmlspecialchars($operatorName); ?></strong>,</p>
                            <p>You need to be assigned to a counter before you can start managing the queue.</p>
                        </div>
                        
                        <div class="assignment-actions">
                            <div class="action-item">
                                <i class="fas fa-user-cog"></i>
                                <div class="action-text">
                                    <h4>Contact Administrator</h4>
                                    <p>Ask your administrator to assign you to a counter in the admin panel.</p>
                                </div>
                            </div>
                            
                            <div class="action-item">
                                <i class="fas fa-tools"></i>
                                <div class="action-text">
                                    <h4>Admin Panel</h4>
                                    <p>If you have admin access, go to Admin > Users to assign a counter.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- <div class="assignment-buttons">
                            <a href="../admin/" class="btn-admin">
                                <i class="fas fa-cog"></i>
                                Go to Admin Panel
                            </a>
                            <a href="../debug_counter.php" class="btn-debug">
                                <i class="fas fa-bug"></i>
                                Debug Assignment
                            </a>
                            <a href="../connections/logout.php" class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div> -->
                        
                        <!-- Debug Info -->
                        <!-- <div class="debug-info">
                            <strong>Debug Info:</strong><br>
                            User ID: <?php echo $userId; ?><br>
                            Counter ID: <?php echo $counterId ?? 'NULL'; ?><br>
                            Counter Name: <?php echo htmlspecialchars($counterName); ?>
                        </div> -->
                    </div>
                </div>
            </section>
            <?php else: ?>
            <!-- Normal Controller Interface -->
            <!-- Queue Numbers Section -->
            <section class="queue-numbers">
                <div class="number-card previous">
                    <div class="card-header">
                        <i class="fas fa-arrow-left"></i>
                        <h3>Previous Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="previousNumber"><?php echo $lastCompletedNumber ? htmlspecialchars($lastCompletedNumber) : '-'; ?></span>
                        <span class="status-text">Completed</span>
                    </div>
                </div>

                <div class="number-card current">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h3>Current Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="currentNumber"><?php echo htmlspecialchars($currentNumber); ?></span>
                        <span class="status-text">Serving</span>
                    </div>
                </div>

                <div class="number-card upcoming">
                    <div class="card-header">
                        <i class="fas fa-arrow-right"></i>
                        <h3>Next Number</h3>
                    </div>
                    <div class="number-display">
                        <span class="number" id="upcomingNumber" data-none="<?php echo $nextAwaitingNumber ? 'false' : 'true'; ?>">
                            <?php echo $nextAwaitingNumber ? htmlspecialchars($nextAwaitingNumber) : 'None'; ?>
                        </span>
                        <span class="status-text <?php echo $nextAwaitingNumber ? '' : 'no-queue'; ?>">
                            <?php echo $nextAwaitingNumber ? 'Waiting' : 'No Queue'; ?>
                        </span>
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
                                placeholder=""
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
                    <span class="queue-count" id="queueCount"><?php echo count($awaitingQueue); ?> customers waiting</span>
                </div>
                <div class="queue-list" id="awaitingList">
                    <?php if (empty($awaitingQueue)): ?>
                        <div class="no-queue">
                            <i class="fas fa-inbox"></i>
                            <p>No customers in queue</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($awaitingQueue as $item): ?>
                            <div class="queue-item">
                                <span class="queue-number"><?php echo htmlspecialchars($item['Awaiting_Number']); ?></span>
                                <span class="service-type"><?php echo htmlspecialchars($counterName); ?></span>
                                <span class="wait-time">-</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>

        <!-- Footer Actions -->
        <footer class="controller-footer">
            <div class="footer-actions">
                <a href="../QueDisplay.php" class="footer-btn">
                    <i class="fas fa-tv"></i>
                    Display View
                </a>
                <a href="../connections/logout.php" class="footer-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </footer>
    </div>

    <!-- Success/Error Messages -->
    <div class="message-container" id="messageContainer">
        <div class="message" id="message"></div>
    </div>

    <!-- Controller Configuration -->
    <script>
        // Pass PHP data to JavaScript
        <?php if ($counterId && $counterName !== 'Not Assigned'): ?>
        window.controllerConfig = {
            currentNumber: '<?php echo addslashes($currentNumber); ?>',
            upcomingNumber: '<?php echo $nextAwaitingNumber ? addslashes($nextAwaitingNumber) : "None"; ?>',
            previousNumber: '<?php echo $lastCompletedNumber ? addslashes($lastCompletedNumber) : "-"; ?>',
            startTime: '<?php echo $startTime ? addslashes($startTime) : ""; ?>',
            isOnBreak: <?php echo ($counterStatus === 'Break') ? 'true' : 'false'; ?>,
            operatorName: '<?php echo addslashes($operatorName); ?>',
            counterName: '<?php echo addslashes($counterName); ?>',
            counterId: <?php echo $counterId; ?>,
            userId: <?php echo $userId; ?>
        };
        <?php endif; ?>
    </script>
</body>
</html>


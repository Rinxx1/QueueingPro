<?php
require_once 'connections/database.php';

// Get counters from database for initial structure
$counters = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            c.Counter_ID,
            c.Counter_Name,
            c.Counter_Status
        FROM counters c
        ORDER BY c.Counter_ID ASC
        LIMIT 6
    ");
    $stmt->execute();
    $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for display
    $counters = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Queue Display - QueueingPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/display.css">
</head>
<body>
    <!-- Main Display Container -->
    <main class="display-container" data-counter-count="<?php echo count($counters); ?>">
        <?php if (count($counters) > 0): ?>
            <?php foreach ($counters as $index => $counter): ?>
                <?php 
                $sectionClass = '';
                if ($index == 0) $sectionClass = 'counter-1';
                elseif ($index == 1) $sectionClass = 'counter-2';
                elseif ($index == 2) $sectionClass = 'counter-3';
                else $sectionClass = 'right-counter-' . ($index - 2);
                ?>
                
                <section class="<?php echo $sectionClass; ?>">
                    <div class="counter-card offline">
                        <div class="counter-header">
                            <div class="counter-left">
                                <div class="counter-number"><?php echo str_pad($counter['Counter_ID'], 2, '0', STR_PAD_LEFT); ?></div>
                                <h3><?php echo htmlspecialchars($counter['Counter_Name']); ?></h3>
                            </div>
                            <div class="counter-status offline">
                                <i class="fas fa-circle"></i>
                                <span>LOADING</span>
                            </div>
                        </div>
                        <div class="counter-info">
                            <div class="current-serving">
                                <span class="label">Now Serving:</span>
                                <span class="queue-number">--</span>
                            </div>
                            <div class="counter-stats">
                                <div class="stat">
                                    <i class="fas fa-users"></i>
                                    <span>Served Today: --</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-clock"></i>
                                    <span>Avg Time: -- min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
            
            <!-- Video section only if we have counters -->
            <section class="video-section">
                <div class="video-container">
                    <div class="video-content">
                        <video id="displayVideo" autoplay muted loop>
                            <source src="uploads/videos/info-video.mp4" type="video/mp4">
                            <div class="video-placeholder">
                                <h2>Video</h2>
                            </div>
                        </video>
                    </div>
                </div>
            </section>
            
        <?php else: ?>
            <!-- No counters available -->
            <section class="no-counters">
                <div class="no-counters-message">
                    <h2>No Counters Available</h2>
                    <p>Please configure counters in the admin panel.</p>
                </div>
            </section>
        <?php endif; ?>

        <!-- Date and Time at Bottom -->
        <section class="datetime-section">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </section>
    </main>

    <script src="js/display.js"></script>
</body>
</html>

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

// Get active video from database
$activeVideo = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM video WHERE Video_Status = 1 LIMIT 1");
    $stmt->execute();
    $activeVideo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently for display
    $activeVideo = null;
}

// Get global mute status and volume
$globalMuted = false;
$globalVolume = 50; // Default volume 50%
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value, Volume FROM settings WHERE setting_key IN ('global_video_muted', 'global_video_volume')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($settings as $setting) {
        if ($setting['setting_key'] === 'global_video_muted') {
            $globalMuted = (bool)$setting['setting_value'];
        } elseif ($setting['setting_key'] === 'global_video_volume') {
            $globalVolume = (int)$setting['Volume'];
        }
    }
} catch(PDOException $e) {
    // Handle error silently, use defaults
    $globalMuted = false;
    $globalVolume = 50;
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
            <?php 
            // First, display counters 1, 2, and 3 individually
            for ($i = 0; $i < min(3, count($counters)); $i++): 
                $counter = $counters[$i];
                $sectionClass = 'counter-' . ($i + 1);
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
            <?php endfor; ?>

            <?php 
            // Then, if there are counters 4, 5, 6, group them in right-counters
            if (count($counters) > 3): 
            ?>
                <section class="right-counters">
                    <?php for ($i = 3; $i < count($counters); $i++): 
                        $counter = $counters[$i];
                    ?>
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
                    <?php endfor; ?>
                </section>
            <?php endif; ?>
            
            <!-- Video Player section only if we have counters -->
            <section class="video-section">
                <div class="video-player-container">
                    <div class="video-content">
                        <video id="displayVideo" autoplay <?php echo $globalMuted ? 'muted' : ''; ?> loop preload="auto" 
                               data-global-muted="<?php echo $globalMuted ? '1' : '0'; ?>" 
                               data-global-volume="<?php echo $globalVolume; ?>">
                            <?php if ($activeVideo && !empty($activeVideo['Video_Location'])): ?>
                                <source src="<?php echo htmlspecialchars($activeVideo['Video_Location']); ?>" type="video/mp4">
                            <?php endif; ?>
                            <!-- Fallback videos -->
                            <source src="uploads/videos/info-video.mp4" type="video/mp4">
                            <source src="uploads/videos/backup-video.mp4" type="video/mp4">
                            <!-- Fallback content -->
                        </video>
                        
                        <!-- Video placeholder/fallback -->
                        <div class="video-placeholder" id="videoPlaceholder" style="display: none;">
                            <div class="placeholder-content">
                                <i class="fas fa-video fa-3x"></i>
                                <h2>Video Player</h2>
                                <?php if ($activeVideo): ?>
                                    <p>Now Playing: <?php echo htmlspecialchars($activeVideo['Video_Title']); ?></p>
                                <?php else: ?>
                                    <p>No active video selected</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Loading indicator -->
                        <div class="video-loading" id="videoLoading" style="display: none;">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Loading video...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress bar with time display -->
                    <div class="video-progress">
                        <div class="video-time-display">
                            <div class="current-video-time" id="videoCurrentTime"></div>
                            <div class="current-video-date" id="videoCurrentDate"></div>
                        </div>
                        <div class="progress-controls">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                            <div class="video-duration">
                                <span id="videoPlayTime">00:00</span> / <span id="videoDuration">00:00</span>
                            </div>
                        </div>
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

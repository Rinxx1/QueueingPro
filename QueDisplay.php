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

// Get counter statistics for today based on Date_Completed
$counterStats = [];
foreach ($counters as $counter) {
    $counterId = $counter['Counter_ID'];
    
    try {
        // Get served today count based on Date_Completed = today's date
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as served_today
            FROM complete 
            WHERE Counter_ID = ? 
            AND Date_Completed = CURDATE()
        ");
        $stmt->execute([$counterId]);
        $servedResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $servedToday = $servedResult['served_today'] ?? 0;
        
        // Get average duration for today based on Date_Completed = today's date
        $stmt = $pdo->prepare("
            SELECT AVG(TIME_TO_SEC(Duration)) as avg_duration_seconds
            FROM complete 
            WHERE Counter_ID = ? 
            AND Date_Completed = CURDATE()
            AND Duration IS NOT NULL
            AND Duration != ''
        ");
        $stmt->execute([$counterId]);
        $durationResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $avgMinutes = 0;
        if ($durationResult['avg_duration_seconds']) {
            $avgSeconds = (float)$durationResult['avg_duration_seconds'];
            $avgMinutes = round($avgSeconds / 60, 1); // Convert to minutes with 1 decimal
        }
        
        $counterStats[$counterId] = [
            'served_today' => $servedToday,
            'avg_time' => $avgMinutes
        ];
    } catch(PDOException $e) {
        $counterStats[$counterId] = [
            'served_today' => 0,
            'avg_time' => 0
        ];
    }
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

// Get global mute status, volume, voice announcements, and dark mode setting
$globalMuted = false;
$globalVolume = 50; // Default volume 50%
$voiceAnnouncements = true; // Default to enabled
$darkModeEnabled = false; // Default to disabled
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value, Volume FROM settings WHERE setting_key IN ('global_video_muted', 'global_video_volume', 'voice_announcements_enabled', 'dark_mode_enabled')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($settings as $setting) {
        if ($setting['setting_key'] === 'global_video_muted') {
            $globalMuted = (bool)$setting['setting_value'];
        } elseif ($setting['setting_key'] === 'global_video_volume') {
            $globalVolume = (int)$setting['Volume'];
        } elseif ($setting['setting_key'] === 'voice_announcements_enabled') {
            $voiceAnnouncements = (bool)$setting['setting_value'];
        } elseif ($setting['setting_key'] === 'dark_mode_enabled') {
            $darkModeEnabled = (bool)$setting['setting_value'];
        }
    }
    
    // Insert default settings if not exists
    if (!array_filter($settings, fn($s) => $s['setting_key'] === 'voice_announcements_enabled')) {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('voice_announcements_enabled', '1')
        ");
        $stmt->execute();
    }
    
    if (!array_filter($settings, fn($s) => $s['setting_key'] === 'dark_mode_enabled')) {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('dark_mode_enabled', '0')
        ");
        $stmt->execute();
    }
} catch(PDOException $e) {
    // Handle error silently, use defaults
    $globalMuted = false;
    $globalVolume = 50;
    $voiceAnnouncements = true;
    $darkModeEnabled = false;
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
    <?php if ($darkModeEnabled): ?>
    <link rel="stylesheet" href="css/display-dark.css">
    <?php endif; ?>
</head>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
    <!-- Main Display Container -->
    <main class="display-container" data-counter-count="<?php echo count($counters); ?>">
        <?php if (count($counters) > 0): ?>
            <?php 
            // First, display counters 1, 2, and 3 individually
            for ($i = 0; $i < min(3, count($counters)); $i++): 
                $counter = $counters[$i];
                $sectionClass = 'counter-' . ($i + 1);
                $stats = $counterStats[$counter['Counter_ID']] ?? ['served_today' => 0, 'avg_time' => 0];
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
                                    <span>Served Today: <?php echo $stats['served_today']; ?></span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-clock"></i>
                                    <span>Avg Time: <?php echo $stats['avg_time']; ?> min</span>
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
                        $stats = $counterStats[$counter['Counter_ID']] ?? ['served_today' => 0, 'avg_time' => 0];
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
                                        <span>Served Today: <?php echo $stats['served_today']; ?></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-clock"></i>
                                        <span>Avg Time: <?php echo $stats['avg_time']; ?> min</span>
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
                        <video id="displayVideo" autoplay muted loop preload="auto" playsinline
                               data-global-muted="<?php echo $globalMuted ? '1' : '0'; ?>" 
                               data-global-volume="<?php echo $globalVolume; ?>"
                               data-voice-announcements="<?php echo $voiceAnnouncements ? '1' : '0'; ?>"
                               data-dark-mode="<?php echo $darkModeEnabled ? '1' : '0'; ?>"
                               data-debug-active-video="<?php echo $activeVideo ? 'true' : 'false'; ?>"
                               data-debug-video-title="<?php echo $activeVideo ? htmlspecialchars($activeVideo['Video_Title']) : 'none'; ?>">
                            <?php if ($activeVideo && !empty($activeVideo['Video_Location'])): ?>
                                <source src="<?php echo htmlspecialchars($activeVideo['Video_Location']); ?>" type="video/mp4">
                                <!-- Debug comment: Active video loaded - <?php echo htmlspecialchars($activeVideo['Video_Title']); ?> -->
                            <?php else: ?>
                                <!-- Debug comment: No active video found in database -->
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
            <!-- <div class="voice-indicator" id="voiceIndicator" style="display: none;">
                <i class="fas fa-volume-up"></i>
                <span>Voice Active</span>
            </div> -->
        </section>
    </main>

    <script src="js/display.js"></script>
</body>
</html>

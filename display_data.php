<?php
require_once 'connections/database.php';

header('Content-Type: application/json');

try {
    // Get all counters with their statistics, limited to 6
    $stmt = $pdo->prepare("
        SELECT 
            c.Counter_ID,
            c.Counter_Name,
            c.Counter_CurrentNumber,
            c.Counter_Status,
            c.Counter_Description
        FROM counters c
        ORDER BY c.Counter_ID ASC
        LIMIT 6
    ");
    $stmt->execute();
    $counterData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $counters = [];
    
    // For each counter, get statistics from complete table
    foreach ($counterData as $counter) {
        $counterId = $counter['Counter_ID'];
        
        // Get served today count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as served_today
            FROM complete 
            WHERE Counter_ID = ? 
            AND DATE(End_Time) = CURDATE()
        ");
        $stmt->execute([$counterId]);
        $servedResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $servedToday = $servedResult['served_today'] ?? 0;
        
        // Get average duration for today
        $stmt = $pdo->prepare("
            SELECT AVG(TIME_TO_SEC(Duration)) as avg_duration_seconds
            FROM complete 
            WHERE Counter_ID = ? 
            AND DATE(End_Time) = CURDATE()
            AND Duration IS NOT NULL
            AND Duration != ''
        ");
        $stmt->execute([$counterId]);
        $durationResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $avgDuration = 0;
        if ($durationResult['avg_duration_seconds']) {
            $avgSeconds = (int)$durationResult['avg_duration_seconds'];
            $avgDuration = round($avgSeconds / 60); // Convert to minutes
        }
        
        $counters[] = [
            'id' => $counter['Counter_ID'],
            'name' => $counter['Counter_Name'],
            'description' => $counter['Counter_Description'] ?? '',
            'current_number' => $counter['Counter_CurrentNumber'] ?? '--',
            'status' => $counter['Counter_Status'] ?? 'Offline',
            'served_today' => $servedToday,
            'avg_duration' => $avgDuration
        ];
    }
    
    // Get active video information
    $activeVideo = null;
    try {
        $stmt = $pdo->prepare("SELECT Video_ID, Video_Title, Video_Description, Video_Location, Video_Status FROM video WHERE Video_Status = 1 LIMIT 1");
        $stmt->execute();
        $videoResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($videoResult) {
            $activeVideo = [
                'id' => $videoResult['Video_ID'],
                'title' => $videoResult['Video_Title'],
                'description' => $videoResult['Video_Description'],
                'location' => $videoResult['Video_Location'],
                'status' => $videoResult['Video_Status']
            ];
        }
    } catch(PDOException $e) {
        // Video error is not critical, continue without video
        error_log('Error fetching video: ' . $e->getMessage());
    }
    
    // Get global mute status, volume, and voice announcements setting
    $globalMuted = false;
    $globalVolume = 50;
    $voiceAnnouncements = true; // Default to enabled
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value, Volume FROM settings WHERE setting_key IN ('global_video_muted', 'global_video_volume', 'voice_announcements_enabled')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($settings as $setting) {
            if ($setting['setting_key'] === 'global_video_muted') {
                $globalMuted = (bool)$setting['setting_value'];
            } elseif ($setting['setting_key'] === 'global_video_volume') {
                $globalVolume = (int)$setting['Volume'];
            } elseif ($setting['setting_key'] === 'voice_announcements_enabled') {
                $voiceAnnouncements = (bool)$setting['setting_value'];
            }
        }
        
        // Insert default voice announcements setting if not exists
        if (!array_filter($settings, fn($s) => $s['setting_key'] === 'voice_announcements_enabled')) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES ('voice_announcements_enabled', '1')
            ");
            $stmt->execute();
        }
    } catch(PDOException $e) {
        // Handle error silently, use defaults
        error_log('Error fetching audio settings: ' . $e->getMessage());
        $globalMuted = false;
        $globalVolume = 50;
        $voiceAnnouncements = true;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $counters,
        'video' => $activeVideo,
        'global_muted' => $globalMuted,
        'global_volume' => $globalVolume,
        'voice_announcements' => $voiceAnnouncements,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>

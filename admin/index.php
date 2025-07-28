<?php
$pageTitle = "Admin Dashboard - QueueingPro";
$currentPage = "dashboard";
include 'components/header.php';
require_once 'dashboard_functions.php';

// Get real-time data
$stats = getDashboardStats();
$recentActivity = getRecentActivity(6);
$counterPerformance = getCounterPerformance();
$awaitingQueue = getAwaitingQueueSummary();
$efficiency = getEfficiencyMetrics();
$hourlyData = getHourlyPerformance();
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard Overview
    </h2>
    <p class="page-description">Real-time monitoring of your queue management system performance and operations.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value" id="customersServed"><?php echo $stats['customers_served']; ?></div>
        <div class="stat-label">Customers Served Today</div>
        <div class="stat-icon"><i class="fas fa-users"></i></div>
    </div>
    <div class="stat-card"> 
        <div class="stat-value" id="customersWaiting"><?php echo $stats['customers_waiting']; ?></div>
        <div class="stat-label">Customers Waiting</div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="activeCounters"><?php echo $stats['active_counters']; ?></div>
        <div class="stat-label">Active Counters</div>
        <div class="stat-icon"><i class="fas fa-desktop"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="avgWaitTime"><?php echo $stats['avg_wait_time']; ?></div>
        <div class="stat-label">Avg Service Time (min)</div>
        <div class="stat-icon"><i class="fas fa-stopwatch"></i></div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Quick Actions -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h3>
        </div>
        <div class="quick-actions" style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="counters.php" class="btn btn-primary">
                <i class="fas fa-desktop"></i>
                Manage Counters
            </a>
            <a href="users.php" class="btn btn-success">
                <i class="fas fa-users"></i>
                Manage Users
            </a>
            <a href="video.php" class="btn btn-info">
                <i class="fas fa-video"></i>
                Video Management
            </a>
            <a href="transactions.php" class="btn btn-secondary">
                <i class="fas fa-list"></i>
                Transaction Log
            </a>
            <a href="../QueDisplay.php" class="btn btn-warning" target="_blank">
                <i class="fas fa-tv"></i>
                View Display
            </a>
            <a href="../controller/" class="btn btn-primary" target="_blank">
                <i class="fas fa-gamepad"></i>
                Controller Panel
            </a>
            <button id="toggleVoiceAnnouncements" class="btn btn-info">
                <i class="fas fa-volume-up"></i>
                <span id="voiceStatus">Voice: ON</span>
            </button>
            <button id="testVoiceAnnouncement" class="btn btn-warning">
                <i class="fas fa-play"></i>
                Test Voice
            </button>
        </div>
    </div>

    <!-- Current Queue Status -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list-ol"></i>
                Current Queue Status
            </h3>
        </div>
        <div class="queue-status">
            <?php if (empty($awaitingQueue)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--medium-gray);">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>No customers currently waiting</p>
                </div>
            <?php else: ?>
                <?php foreach ($awaitingQueue as $queue): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <div style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($queue['Counter_Name']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--medium-gray);">Next: <?php echo htmlspecialchars($queue['next_number']); ?></div>
                        </div>
                        <div class="queue-count-badge"><?php echo $queue['queue_count']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clock"></i>
                Recent Activity
            </h3>
        </div>
        <div class="recent-activity">
            <?php if (empty($recentActivity)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--medium-gray);">
                    <i class="fas fa-history" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>No recent activity today</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivity as $activity): ?>
                    <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: 600; color: var(--primary-color);">
                            <?php echo htmlspecialchars($activity['Complete_Number']); ?>
                            <?php if ($activity['Firstname']): ?>
                                - <?php echo htmlspecialchars($activity['Firstname'] . ' ' . $activity['Lastname']); ?>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--medium-gray);">
                            <?php echo htmlspecialchars($activity['Counter_Name'] ?? 'Unknown Counter'); ?> • 
                            Duration: <?php echo htmlspecialchars($activity['Duration'] ?? 'N/A'); ?> • 
                            <?php echo abs($activity['minutes_ago'] ?? 0); ?> minutes ago
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Counter Performance -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i>
                Counter Performance Today
            </h3>
        </div>
        <div class="counter-performance">
            <?php if (empty($counterPerformance)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--medium-gray);">
                    <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>No counter data available</p>
                </div>
            <?php else: ?>
                <?php foreach ($counterPerformance as $counter): ?>
                    <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <div style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($counter['Counter_Name']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--medium-gray);">
                                <?php echo htmlspecialchars($counter['operator_name'] ?? 'No operator assigned'); ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--success-color);"><?php echo $counter['customers_served']; ?></div>
                            <div style="font-size: 0.8rem; color: var(--medium-gray);">Served</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--accent-color);"><?php echo round($counter['avg_service_time'] ?? 0, 1); ?>m</div>
                            <div style="font-size: 0.8rem; color: var(--medium-gray);">Avg Time</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Today's Performance Summary
            </h3>
        </div>
        <div class="performance-summary">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);"><?php echo $efficiency['service_efficiency']; ?>%</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Service Efficiency</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);"><?php echo $efficiency['avg_service_time']; ?>m</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Avg Service Time</div>
                </div>
            </div>
            
            <?php if (!empty($hourlyData)): ?>
                <div style="background: var(--light-gray); padding: 1rem; border-radius: 8px;">
                    <h4 style="margin: 0 0 1rem 0; color: var(--primary-color);">Hourly Activity</h4>
                    <div style="display: flex; gap: 0.5rem; align-items: end; height: 60px;">
                        <?php foreach ($hourlyData as $hour): ?>
                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                                <div style="background: var(--primary-color); width: 100%; height: <?php echo min(50, $hour['customers_served'] * 10); ?>px; margin-bottom: 5px; border-radius: 2px;"></div>
                                <div style="font-size: 0.7rem; color: var(--medium-gray);"><?php echo $hour['hour']; ?>h</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: var(--light-gray); padding: 1rem; border-radius: 8px; text-align: center;">
                    <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--medium-gray); margin-bottom: 0.5rem;"></i>
                    <div style="color: var(--medium-gray); font-size: 0.9rem;">No hourly data available yet</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Auto-refresh Script -->
<script>
// Auto-refresh dashboard data every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);

// Add some visual feedback for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Add a refresh indicator
    const pageHeader = document.querySelector('.page-header .page-description');
    if (pageHeader) {
        const lastUpdate = new Date().toLocaleTimeString();
        pageHeader.innerHTML += ` <small style="color: var(--medium-gray);">(Last updated: ${lastUpdate})</small>`;
    }
    
    // Voice announcements toggle functionality
    const toggleButton = document.getElementById('toggleVoiceAnnouncements');
    const voiceStatus = document.getElementById('voiceStatus');
    
    if (toggleButton && voiceStatus) {
        // Get current voice announcements status
        fetch('../display_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.hasOwnProperty('voice_announcements')) {
                    updateVoiceButton(data.voice_announcements);
                }
            })
            .catch(error => console.error('Error fetching voice status:', error));
        
        toggleButton.addEventListener('click', function() {
            const currentStatus = voiceStatus.textContent.includes('ON');
            const newStatus = !currentStatus;
            
            // Update the setting via AJAX
            fetch('voice_announcements_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle&enabled=' + (newStatus ? '1' : '0')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateVoiceButton(newStatus);
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success!',
                            text: `Voice announcements ${newStatus ? 'enabled' : 'disabled'}`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    console.error('Error updating voice announcements:', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating voice announcements:', error);
            });
        });
        
        function updateVoiceButton(enabled) {
            if (enabled) {
                voiceStatus.textContent = 'Voice: ON';
                toggleButton.className = 'btn btn-info';
                toggleButton.querySelector('i').className = 'fas fa-volume-up';
            } else {
                voiceStatus.textContent = 'Voice: OFF';
                toggleButton.className = 'btn btn-secondary';
                toggleButton.querySelector('i').className = 'fas fa-volume-mute';
            }
        }
        
        // Test voice announcement functionality
        const testButton = document.getElementById('testVoiceAnnouncement');
        if (testButton) {
            testButton.addEventListener('click', function() {
                if (window.speechSynthesis) {
                    const utterance = new SpeechSynthesisUtterance('Number 123');
                    utterance.rate = 0.6;
                    utterance.pitch = 1.0;
                    utterance.volume = 1.0;
                    
                    // Try to use a clear voice
                    const voices = window.speechSynthesis.getVoices();
                    const preferredVoice = voices.find(voice => 
                        voice.lang.includes('en') && 
                        (voice.name.includes('Google') || voice.name.includes('Microsoft') || voice.name.includes('Samantha'))
                    );
                    
                    if (preferredVoice) {
                        utterance.voice = preferredVoice;
                    }
                    
                    window.speechSynthesis.speak(utterance);
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Test Announcement',
                            text: 'Voice test initiated. You should hear "Number 123".',
                            icon: 'info',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    // Show error message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Speech Synthesis Not Supported',
                            text: 'Your browser does not support speech synthesis.',
                            icon: 'error',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }
    }
});
</script>

<!-- Custom Dashboard Styles -->
<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    position: relative;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--medium-gray);
    font-weight: 500;
}

.stat-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    color: var(--primary-color);
    opacity: 0.3;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.queue-count-badge {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-online, .status-connected, .status-active, .status-playing, .status-working {
    background: var(--success-color);
    color: white;
}

.status-idle, .status-standby, .status-ready {
    background: var(--warning-color);
    color: white;
}

.status-error, .status-unknown {
    background: var(--danger-color);
    color: white;
}
</style>

<?php include 'components/footer.php'; ?>

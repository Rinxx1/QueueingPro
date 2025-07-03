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
    <main class="display-container">
        <!-- Counter 1 - Top Left -->
        <section class="counter-1">
            <div class="counter-card active">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">01</div>
                        <h3>General Banking</h3>
                    </div>
                    <div class="counter-status online">
                        <i class="fas fa-circle"></i>
                        <span>ONLINE</span>
                    </div>
                </div>
                <div class="counter-info">
                    <div class="current-serving">
                        <span class="label">Now Serving:</span>
                        <span class="queue-number">A031</span>
                    </div>
                    <div class="counter-stats">
                        <div class="stat">
                            <i class="fas fa-users"></i>
                            <span>Served Today: 11</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: 18 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Counter 2 - Bottom Left -->
        <section class="counter-2">
            <div class="counter-card active">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">02</div>
                        <h3>Teller Services</h3>
                    </div>
                    <div class="counter-status online">
                        <i class="fas fa-circle"></i>
                        <span>ONLINE</span>
                    </div>
                </div>
                <div class="counter-info">
                    <div class="current-serving">
                        <span class="label">Now Serving:</span>
                        <span class="queue-number">B045</span>
                    </div>
                    <div class="counter-stats">
                        <div class="stat">
                            <i class="fas fa-users"></i>
                            <span>Served Today: 23</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: 15 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Center - Video Display -->
        <section class="video-section">
            <div class="video-container">
                <div class="video-content">
                    <video id="displayVideo" autoplay muted loop>
                        <source src="video/info-video.mp4" type="video/mp4">
                        <div class="video-placeholder">
                            <h2>Video</h2>
                        </div>
                    </video>
                </div>
            </div>
        </section>

        <!-- Counter 3 - Bottom Left -->
        <section class="counter-3">
            <div class="counter-card active">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">03</div>
                        <h3>Loan Services</h3>
                    </div>
                    <div class="counter-status online">
                        <i class="fas fa-circle"></i>
                        <span>ONLINE</span>
                    </div>
                </div>
                <div class="counter-info">
                    <div class="current-serving">
                        <span class="label">Now Serving:</span>
                        <span class="queue-number">C012</span>
                    </div>
                    <div class="counter-stats">
                        <div class="stat">
                            <i class="fas fa-users"></i>
                            <span>Served Today: 8</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: 25 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Side - Counters 4, 5, 6 Horizontally -->
        <section class="right-counters">
            <!-- Counter 4 -->
            <div class="counter-card offline">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">04</div>
                        <h3>Customer Support</h3>
                    </div>
                    <div class="counter-status offline">
                        <i class="fas fa-circle"></i>
                        <span>OFFLINE</span>
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
                            <span>Served Today: 0</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: -- min</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Counter 5 -->
            <div class="counter-card offline">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">05</div>
                        <h3>Business Banking</h3>
                    </div>
                    <div class="counter-status offline">
                        <i class="fas fa-circle"></i>
                        <span>OFFLINE</span>
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
                            <span>Served Today: 0</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: -- min</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Counter 6 -->
            <div class="counter-card active">
                <div class="counter-header">
                    <div class="counter-left">
                        <div class="counter-number">06</div>
                        <h3>Premium Services</h3>
                    </div>
                    <div class="counter-status online">
                        <i class="fas fa-circle"></i>
                        <span>ONLINE</span>
                    </div>
                </div>
                <div class="counter-info">
                    <div class="current-serving">
                        <span class="label">Now Serving:</span>
                        <span class="queue-number">E002</span>
                    </div>
                    <div class="counter-stats">
                        <div class="stat">
                            <i class="fas fa-users"></i>
                            <span>Served Today: 5</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-clock"></i>
                            <span>Avg Time: 25 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Date and Time at Bottom -->
        <section class="datetime-section">
            <div class="current-time" id="currentTime"></div>
            <div class="current-date" id="currentDate"></div>
        </section>
    </main>

    <script src="js/display.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Sync Test - QueueingPro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn.danger {
            background: #f44336;
        }
        .btn.danger:hover {
            background: #da190b;
        }
        .log {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            margin-top: 20px;
        }
        .current-video {
            background: #e8f5e8;
            border: 1px solid #4CAF50;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎥 Video Auto-Sync Test</h1>
        
        <div class="status">
            <h3>Current Status</h3>
            <div id="currentStatus">Loading...</div>
        </div>
        
        <div class="current-video">
            <h4>Currently Active Video:</h4>
            <div id="currentVideo">Checking...</div>
        </div>
        
        <div>
            <h3>Test Scenarios</h3>
            <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;">
                <h4>🎥 Video State Testing:</h4>
                <ul>
                    <li><strong>No Active Video:</strong> Should show "Loading video..." with "No video currently active" or "Checking for active videos..."</li>
                    <li><strong>Video Activated:</strong> Should switch to video playback automatically</li>
                    <li><strong>Video Deactivated:</strong> Should return to "Loading video..." state</li>
                    <li><strong>Video Changed:</strong> Should seamlessly switch between videos</li>
                </ul>
            </div>
            
            <h3>Test Controls</h3>
            <button class="btn" onclick="checkCurrentVideo()">Check Current Video</button>
            <button class="btn" onclick="testDataFetch()">Test Data Fetch</button>
            <button class="btn danger" onclick="clearLog()">Clear Log</button>
            <button class="btn" onclick="openAdminPanel()">Open Admin Panel</button>
            <button class="btn" onclick="openDisplay()">Open Queue Display</button>
        </div>
        
        <div>
            <h3>Instructions</h3>
            <ol>
                <li>Open the <strong>Admin Panel</strong> in another tab</li>
                <li>Go to <strong>Video Management</strong></li>
                <li><strong>Test Scenario 1:</strong> Make sure no videos are active → Display should show "Loading video..." with appropriate message</li>
                <li><strong>Test Scenario 2:</strong> Activate a video → Display should automatically start playing it</li>
                <li><strong>Test Scenario 3:</strong> Deactivate the video → Display should return to "Loading video..." state</li>
                <li><strong>Test Scenario 4:</strong> Switch between different videos → Display should change seamlessly</li>
                <li>Watch this test page to monitor all changes in real-time</li>
            </ol>
        </div>
        
        <div class="log" id="log">
            <div>Video Auto-Sync Test Console</div>
            <div>===============================</div>
        </div>
    </div>

    <script>
        let updateInterval;
        
        function log(message) {
            const logDiv = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            logDiv.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function clearLog() {
            document.getElementById('log').innerHTML = `
                <div>Video Auto-Sync Test Console</div>
                <div>===============================</div>
            `;
        }
        
        async function checkCurrentVideo() {
            try {
                log('Checking current video...');
                const response = await fetch('display_data.php');
                const result = await response.json();
                
                if (result.success) {
                    const video = result.video;
                    const videoInfo = video ? 
                        `ID: ${video.id}, Title: "${video.title}", Location: ${video.location}` : 
                        'No active video';
                    
                    document.getElementById('currentVideo').innerHTML = videoInfo;
                    log(`Current video: ${videoInfo}`);
                } else {
                    log(`Error: ${result.message}`);
                }
            } catch (error) {
                log(`Fetch error: ${error.message}`);
            }
        }
        
        async function testDataFetch() {
            try {
                log('Testing data fetch...');
                const response = await fetch('display_data.php');
                const result = await response.json();
                
                log(`Response: ${JSON.stringify(result, null, 2)}`);
            } catch (error) {
                log(`Error: ${error.message}`);
            }
        }
        
        function openAdminPanel() {
            window.open('admin/video.php', '_blank');
        }
        
        function openDisplay() {
            window.open('QueDisplay.php', '_blank');
        }
        
        function startMonitoring() {
            log('Starting video monitoring (every 3 seconds)...');
            
            let lastVideoId = null;
            let lastVideoLocation = null;
            
            updateInterval = setInterval(async () => {
                try {
                    const response = await fetch('display_data.php');
                    const result = await response.json();
                    
                    if (result.success && result.video) {
                        const video = result.video;
                        
                        // Check for changes
                        if (lastVideoId !== video.id || lastVideoLocation !== video.location) {
                            log(`📺 VIDEO CHANGED! From: ID=${lastVideoId} → To: ID=${video.id}, Title="${video.title}"`);
                            
                            lastVideoId = video.id;
                            lastVideoLocation = video.location;
                            
                            document.getElementById('currentVideo').innerHTML = 
                                `ID: ${video.id}, Title: "${video.title}", Location: ${video.location}`;
                        }
                    } else if (result.success && !result.video) {
                        if (lastVideoId !== null) {
                            log('📺 VIDEO DEACTIVATED! No active video');
                            lastVideoId = null;
                            lastVideoLocation = null;
                            document.getElementById('currentVideo').innerHTML = 'No active video';
                        }
                    }
                    
                    document.getElementById('currentStatus').innerHTML = 
                        `<span style="color: green;">✅ Connected</span> (Last update: ${result.timestamp})`;
                        
                } catch (error) {
                    document.getElementById('currentStatus').innerHTML = 
                        `<span style="color: red;">❌ Connection Error</span>`;
                    log(`Monitoring error: ${error.message}`);
                }
            }, 3000);
        }
        
        // Start monitoring when page loads
        window.addEventListener('load', () => {
            log('Page loaded, initializing...');
            checkCurrentVideo();
            startMonitoring();
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
</body>
</html>

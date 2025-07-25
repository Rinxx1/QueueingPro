<?php
require_once '../connections/database.php';

function getAwaitingQueueForCounter($counterId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Awaiting_ID, Awaiting_Number, Counter_ID
            FROM awaiting
            WHERE Counter_ID = ?
            ORDER BY Awaiting_Number ASC
        ");
        $stmt->execute([$counterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function removeSpecificFromAwaitingQueue($counterId, $awaitingNumber) {
    global $pdo;
    try {
        // Remove the specific number from the awaiting queue
        $stmt = $pdo->prepare("
            DELETE FROM awaiting 
            WHERE Counter_ID = ? AND Awaiting_Number = ?
        ");
        $stmt->execute([$counterId, $awaitingNumber]);
        
        return $stmt->rowCount() > 0; // Return true if a row was actually deleted
    } catch(PDOException $e) {
        return false;
    }
}

function removeFirstFromAwaitingQueue($counterId) {
    global $pdo;
    try {
        // Get the first item in the queue
        $stmt = $pdo->prepare("
            SELECT Awaiting_ID
            FROM awaiting
            WHERE Counter_ID = ?
            ORDER BY Awaiting_Number ASC
            LIMIT 1
        ");
        $stmt->execute([$counterId]);
        $firstItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($firstItem) {
            // Remove the first item
            $stmt = $pdo->prepare("DELETE FROM awaiting WHERE Awaiting_ID = ?");
            $stmt->execute([$firstItem['Awaiting_ID']]);
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function getNextAwaitingNumber($counterId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Awaiting_Number
            FROM awaiting
            WHERE Counter_ID = ?
            ORDER BY Awaiting_Number ASC
            LIMIT 1
        ");
        $stmt->execute([$counterId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['Awaiting_Number'] : null;
    } catch(PDOException $e) {
        return null;
    }
}

function saveCompletedNumber($counterNumber, $counterId) {
    global $pdo;
    try {
        // Check if this is an initial number (like A000, B000, C000, etc.)
        $isInitialNumber = preg_match('/^[A-Z]000$/', $counterNumber);
        
        $currentDate = date('Y-m-d');
        
        if ($isInitialNumber) {
            // For initial numbers (A000, B000, etc.), only save basic completion record
            // No timing data needed for these placeholder numbers
            $stmt = $pdo->prepare("
                INSERT INTO complete (Complete_Number, Counter_ID, Date_Completed)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$counterNumber, $counterId, $currentDate]);
        } else {
            // For regular customer numbers, save full completion record with timing
            $currentTime = date('H:i:s');
            
            // Get the start time from the counter for duration calculation
            $stmt = $pdo->prepare("SELECT Start_Time FROM counters WHERE Counter_ID = ?");
            $stmt->execute([$counterId]);
            $counter = $stmt->fetch(PDO::FETCH_ASSOC);
            $startTime = $counter ? $counter['Start_Time'] : null;
            
            $duration = '00:05:00'; // Default duration
            
            if ($startTime) {
                // Calculate duration
                $start = new DateTime($startTime);
                $end = new DateTime($currentTime);
                $interval = $start->diff($end);
                $calculatedDuration = $interval->format('%H:%I:%S');
                $durationInMinutes = ($interval->h * 60) + $interval->i;
                
                // Use calculated duration if reasonable (less than 1 hour)
                if ($durationInMinutes <= 60) {
                    $duration = $calculatedDuration;
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO complete (Complete_Number, Counter_ID, End_Time, Duration, Date_Completed)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$counterNumber, $counterId, $currentTime, $duration, $currentDate]);
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function getCompletedNumbers($counterId, $limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Complete_ID, Complete_Number, Counter_ID, End_Time, Duration, Date_Completed
            FROM complete
            WHERE Counter_ID = ?
            ORDER BY Complete_ID DESC
            LIMIT ?
        ");
        $stmt->execute([$counterId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function getLastCompletedNumber($counterId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Complete_Number
            FROM complete
            WHERE Counter_ID = ?
            ORDER BY Complete_ID DESC
            LIMIT 1
        ");
        $stmt->execute([$counterId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['Complete_Number'] : null;
    } catch(PDOException $e) {
        return null;
    }
}

function initializeCounterForDay($counterId) {
    global $pdo;
    try {
        // Clear any existing completed entries for today and reset timing
        $currentTime = date('H:i:s');
        $stmt = $pdo->prepare("UPDATE counters SET Start_Time = ?, Counter_CurrentNumber = 'A001' WHERE Counter_ID = ?");
        $stmt->execute([$currentTime, $counterId]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function updateCounterStartTime($counterId) {
    global $pdo;
    try {
        $currentTime = date('H:i:s');
        $stmt = $pdo->prepare("UPDATE counters SET Start_Time = ? WHERE Counter_ID = ?");
        $stmt->execute([$currentTime, $counterId]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
?>
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
        // Get the start time from the counter
        $stmt = $pdo->prepare("SELECT Start_Time FROM counters WHERE Counter_ID = ?");
        $stmt->execute([$counterId]);
        $counter = $stmt->fetch(PDO::FETCH_ASSOC);
        $startTime = $counter ? $counter['Start_Time'] : null;
        
        // Calculate current time and duration
        $currentTime = date('H:i:s');
        $duration = null;
        
        if ($startTime) {
            // Calculate duration in minutes and seconds
            $start = new DateTime($startTime);
            $end = new DateTime($currentTime);
            $interval = $start->diff($end);
            $duration = $interval->format('%H:%I:%S');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO complete (Complete_Number, Counter_ID, End_Time, Duration)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$counterNumber, $counterId, $currentTime, $duration]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function getCompletedNumbers($counterId, $limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Complete_ID, Complete_Number, Counter_ID, End_Time, Duration
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
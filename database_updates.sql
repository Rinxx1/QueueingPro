-- QueueingPro Database Updates
-- Update transactions table structure to match the actual system flow

-- Create or update the transactions table
-- This table will serve as a unified view of all transactions (both awaiting and completed)

-- Drop existing transactions table if it exists with wrong structure
DROP TABLE IF EXISTS `transactions`;

-- Create the new transactions table that matches the system flow
CREATE TABLE `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `queue_number` varchar(10) NOT NULL,
    `counter_id` int(11) DEFAULT NULL,
    `operator_id` int(11) DEFAULT NULL,
    `status` enum('Awaiting','Complete') DEFAULT 'Awaiting',
    `time_created` timestamp DEFAULT CURRENT_TIMESTAMP,
    `time_served` timestamp NULL DEFAULT NULL,
    `duration` time NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `queue_number` (`queue_number`),
    KEY `counter_id` (`counter_id`),
    KEY `operator_id` (`operator_id`),
    KEY `status` (`status`),
    KEY `time_created` (`time_created`),
    FOREIGN KEY (`counter_id`) REFERENCES `counters` (`Counter_ID`) ON DELETE SET NULL,
    FOREIGN KEY (`operator_id`) REFERENCES `users` (`User_ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create indexes for better performance
CREATE INDEX `idx_transactions_status_date` ON `transactions` (`status`, `time_created`);
CREATE INDEX `idx_transactions_counter_date` ON `transactions` (`counter_id`, `time_created`);

-- Create a view that combines data from awaiting and complete tables
-- This view will be used by the transaction management system
CREATE OR REPLACE VIEW `v_all_transactions` AS
(
    SELECT 
        a.Awaiting_ID as transaction_id,
        c.Counter_CurrentNumber as queue_number,
        a.Counter_ID as counter_id,
        a.User_ID as operator_id,
        c.Counter_Name,
        u.Username as operator_name,
        'Awaiting' as status,
        a.Start_Time as time_created,
        NULL as time_served,
        NULL as duration,
        '00:00:00' as formatted_duration,
        a.Start_Time as created_at
    FROM awaiting a
    LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
    LEFT JOIN users u ON a.User_ID = u.User_ID
)
UNION ALL
(
    SELECT 
        comp.Complete_ID as transaction_id,
        comp.Complete_Number as queue_number,
        comp.Counter_ID as counter_id,
        NULL as operator_id,
        c.Counter_Name,
        'System' as operator_name,
        'Complete' as status,
        NULL as time_created,
        comp.End_Time as time_served,
        comp.Duration as duration,
        CONCAT(
            LPAD(HOUR(comp.Duration), 2, '0'), ':',
            LPAD(MINUTE(comp.Duration), 2, '0'), ':',
            LPAD(SECOND(comp.Duration), 2, '0')
        ) as formatted_duration,
        comp.End_Time as created_at
    FROM complete comp
    LEFT JOIN counters c ON comp.Counter_ID = c.Counter_ID
)
ORDER BY created_at DESC;

-- Create triggers to automatically sync transactions table with awaiting and complete tables

-- Trigger for when a new entry is added to awaiting table
DELIMITER $$
CREATE TRIGGER `sync_awaiting_insert` 
AFTER INSERT ON `awaiting` 
FOR EACH ROW 
BEGIN
    INSERT INTO `transactions` (
        queue_number, 
        counter_id, 
        operator_id, 
        status, 
        time_created
    ) 
    VALUES (
        (SELECT Counter_CurrentNumber FROM counters WHERE Counter_ID = NEW.Counter_ID),
        NEW.Counter_ID,
        NEW.User_ID,
        'Awaiting',
        NEW.Start_Time
    )
    ON DUPLICATE KEY UPDATE
        counter_id = NEW.Counter_ID,
        operator_id = NEW.User_ID,
        status = 'Awaiting',
        time_created = NEW.Start_Time,
        updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger for when an entry is completed (moved from awaiting to complete)
CREATE TRIGGER `sync_complete_insert` 
AFTER INSERT ON `complete` 
FOR EACH ROW 
BEGIN
    INSERT INTO `transactions` (
        queue_number, 
        counter_id, 
        status, 
        time_served, 
        duration
    ) 
    VALUES (
        NEW.Complete_Number,
        NEW.Counter_ID,
        'Complete',
        NEW.End_Time,
        NEW.Duration
    )
    ON DUPLICATE KEY UPDATE
        status = 'Complete',
        time_served = NEW.End_Time,
        duration = NEW.Duration,
        updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger for when an awaiting entry is deleted (completed)
CREATE TRIGGER `sync_awaiting_delete` 
AFTER DELETE ON `awaiting` 
FOR EACH ROW 
BEGIN
    -- Don't delete from transactions, just update if exists
    UPDATE `transactions` 
    SET updated_at = CURRENT_TIMESTAMP
    WHERE queue_number = (
        SELECT Counter_CurrentNumber 
        FROM counters 
        WHERE Counter_ID = OLD.Counter_ID
    );
END$$

DELIMITER ;

-- Insert sample data to match the existing system
-- This will populate the transactions table with existing data
INSERT INTO `transactions` (queue_number, counter_id, operator_id, status, time_created)
SELECT 
    c.Counter_CurrentNumber,
    a.Counter_ID,
    a.User_ID,
    'Awaiting',
    a.Start_Time
FROM awaiting a
LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
ON DUPLICATE KEY UPDATE
    counter_id = VALUES(counter_id),
    operator_id = VALUES(operator_id),
    status = 'Awaiting',
    time_created = VALUES(time_created);

-- Insert completed transactions
INSERT INTO `transactions` (queue_number, counter_id, status, time_served, duration)
SELECT 
    comp.Complete_Number,
    comp.Counter_ID,
    'Complete',
    comp.End_Time,
    comp.Duration
FROM complete comp
ON DUPLICATE KEY UPDATE
    status = 'Complete',
    time_served = VALUES(time_served),
    duration = VALUES(duration);

-- Create a stored procedure for transaction statistics
DELIMITER $$
CREATE PROCEDURE `GetTransactionStats`(IN period_filter VARCHAR(10))
BEGIN
    DECLARE start_date DATETIME;
    
    -- Set date range based on period
    CASE period_filter
        WHEN 'today' THEN SET start_date = CURDATE();
        WHEN 'week' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 7 DAY);
        WHEN 'month' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 30 DAY);
        ELSE SET start_date = CURDATE();
    END CASE;
    
    SELECT 
        COUNT(*) as total_today,
        SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed_today,
        SUM(CASE WHEN status = 'Awaiting' THEN 1 ELSE 0 END) as in_progress,
        COALESCE(AVG(CASE 
            WHEN status = 'Complete' AND duration IS NOT NULL 
            THEN TIME_TO_SEC(duration)/60 
            ELSE NULL 
        END), 0) as average_wait_time,
        -- Efficiency metrics
        ROUND(
            (SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) * 100.0) / 
            NULLIF(COUNT(*), 0), 2
        ) as completion_rate,
        ROUND(COALESCE(AVG(CASE 
            WHEN status = 'Complete' AND duration IS NOT NULL 
            THEN TIME_TO_SEC(duration)/60 
            ELSE NULL 
        END), 0), 1) as avg_service_time,
        ROUND(
            (SUM(CASE 
                WHEN status = 'Complete' AND duration IS NOT NULL 
                AND TIME_TO_SEC(duration) < 300 THEN 1 ELSE 0 
            END) * 100.0) / 
            NULLIF(SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END), 0), 0
        ) as fast_service,
        ROUND(
            (SUM(CASE 
                WHEN status = 'Complete' AND duration IS NOT NULL 
                AND TIME_TO_SEC(duration) BETWEEN 300 AND 900 THEN 1 ELSE 0 
            END) * 100.0) / 
            NULLIF(SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END), 0), 0
        ) as standard_service,
        ROUND(
            (SUM(CASE 
                WHEN status = 'Complete' AND duration IS NOT NULL 
                AND TIME_TO_SEC(duration) > 900 THEN 1 ELSE 0 
            END) * 100.0) / 
            NULLIF(SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END), 0), 0
        ) as slow_service
    FROM transactions 
    WHERE DATE(COALESCE(time_served, time_created)) >= start_date;
END$$

DELIMITER ;

-- QueueingPro Complete Database Setup
-- This script creates the transactions table and adds foreign keys to existing tables

-- ===================================
-- 1. CREATE TRANSACTIONS TABLE
-- ===================================

CREATE TABLE `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `queue_number` varchar(10) NOT NULL,
    `counter_id` int(11) DEFAULT NULL,
    `operator_id` int(11) DEFAULT NULL,
    `status` enum('Awaiting','Complete','Cancelled') DEFAULT 'Awaiting',
    `time_created` timestamp DEFAULT CURRENT_TIMESTAMP,
    `time_served` timestamp NULL DEFAULT NULL,
    `duration` time NULL DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_queue_number` (`queue_number`),
    KEY `idx_counter_id` (`counter_id`),
    KEY `idx_operator_id` (`operator_id`),
    KEY `idx_status` (`status`),
    KEY `idx_time_created` (`time_created`),
    KEY `idx_status_date` (`status`, `time_created`),
    KEY `idx_counter_date` (`counter_id`, `time_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===================================
-- 2. ADD FOREIGN KEYS TO TRANSACTIONS TABLE
-- ===================================

-- Add foreign key constraint to counters table
ALTER TABLE `transactions` 
ADD CONSTRAINT `fk_transactions_counter` 
FOREIGN KEY (`counter_id`) REFERENCES `counters` (`Counter_ID`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Add foreign key constraint to users table
ALTER TABLE `transactions` 
ADD CONSTRAINT `fk_transactions_operator` 
FOREIGN KEY (`operator_id`) REFERENCES `users` (`User_ID`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ===================================
-- 3. ADD FOREIGN KEYS TO EXISTING TABLES
-- ===================================

-- Add foreign keys to AWAITING table (if not already present)
-- First, check and add foreign key to counters
ALTER TABLE `awaiting` 
ADD CONSTRAINT `fk_awaiting_counter` 
FOREIGN KEY (`Counter_ID`) REFERENCES `counters` (`Counter_ID`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Add foreign key to users table for awaiting
ALTER TABLE `awaiting` 
ADD CONSTRAINT `fk_awaiting_user` 
FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Add foreign keys to COMPLETE table (if not already present)
ALTER TABLE `complete` 
ADD CONSTRAINT `fk_complete_counter` 
FOREIGN KEY (`Counter_ID`) REFERENCES `counters` (`Counter_ID`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Add foreign keys to COUNTERS table (if not already present)
-- Add foreign key for operator assignment
ALTER TABLE `counters` 
ADD CONSTRAINT `fk_counters_user` 
FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ===================================
-- 4. CREATE UNIFIED TRANSACTIONS VIEW
-- ===================================

CREATE OR REPLACE VIEW `v_all_transactions` AS
(
    -- Active/Awaiting transactions
    SELECT 
        CONCAT('A-', a.Awaiting_ID) as transaction_ref,
        c.Counter_CurrentNumber as queue_number,
        a.Counter_ID as counter_id,
        a.User_ID as operator_id,
        c.Counter_Name,
        COALESCE(u.Username, 'Unassigned') as operator_name,
        'Awaiting' as status,
        a.Start_Time as time_created,
        NULL as time_served,
        NULL as duration,
        '00:00:00' as formatted_duration,
        a.Start_Time as created_at,
        NULL as notes
    FROM awaiting a
    LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
    LEFT JOIN users u ON a.User_ID = u.User_ID
)
UNION ALL
(
    -- Completed transactions
    SELECT 
        CONCAT('C-', comp.Complete_ID) as transaction_ref,
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
        comp.End_Time as created_at,
        NULL as notes
    FROM complete comp
    LEFT JOIN counters c ON comp.Counter_ID = c.Counter_ID
)
UNION ALL
(
    -- Manual transactions from transactions table
    SELECT 
        CONCAT('T-', t.id) as transaction_ref,
        t.queue_number,
        t.counter_id,
        t.operator_id,
        c.Counter_Name,
        COALESCE(u.Username, 'Unassigned') as operator_name,
        t.status,
        t.time_created,
        t.time_served,
        t.duration,
        CASE 
            WHEN t.duration IS NOT NULL THEN
                CONCAT(
                    LPAD(HOUR(t.duration), 2, '0'), ':',
                    LPAD(MINUTE(t.duration), 2, '0'), ':',
                    LPAD(SECOND(t.duration), 2, '0')
                )
            ELSE '00:00:00'
        END as formatted_duration,
        t.created_at,
        t.notes
    FROM transactions t
    LEFT JOIN counters c ON t.counter_id = c.Counter_ID
    LEFT JOIN users u ON t.operator_id = u.User_ID
)
ORDER BY created_at DESC;

-- ===================================
-- 5. CREATE STORED PROCEDURES FOR ANALYTICS
-- ===================================

DELIMITER $$

-- Procedure to get transaction statistics
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
        -- Basic counts
        COUNT(*) as total_today,
        SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed_today,
        SUM(CASE WHEN status = 'Awaiting' THEN 1 ELSE 0 END) as in_progress,
        
        -- Average wait time in minutes
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
        
        -- Service speed categories
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
        
    FROM v_all_transactions 
    WHERE DATE(COALESCE(time_served, time_created)) >= start_date;
END$$

-- Procedure to get counter analytics
CREATE PROCEDURE `GetCounterAnalytics`(IN period_filter VARCHAR(10))
BEGIN
    DECLARE start_date DATETIME;
    
    CASE period_filter
        WHEN 'today' THEN SET start_date = CURDATE();
        WHEN 'week' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 7 DAY);
        WHEN 'month' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 30 DAY);
        ELSE SET start_date = CURDATE();
    END CASE;
    
    SELECT 
        Counter_Name as counter_name,
        counter_id,
        COUNT(*) as count,
        ROUND(
            (COUNT(*) * 100.0) / 
            (SELECT COUNT(*) FROM v_all_transactions 
             WHERE DATE(COALESCE(time_served, time_created)) >= start_date), 2
        ) as percentage,
        AVG(CASE 
            WHEN duration IS NOT NULL THEN TIME_TO_SEC(duration)/60 
            ELSE NULL 
        END) as avg_duration
    FROM v_all_transactions 
    WHERE DATE(COALESCE(time_served, time_created)) >= start_date
        AND counter_id IS NOT NULL
    GROUP BY counter_id, Counter_Name
    ORDER BY count DESC;
END$$

DELIMITER ;

-- ===================================
-- 6. CREATE TRIGGERS FOR DATA SYNCHRONIZATION
-- ===================================

DELIMITER $$

-- Trigger: Sync when new awaiting entry is created
CREATE TRIGGER `sync_awaiting_to_transactions` 
AFTER INSERT ON `awaiting` 
FOR EACH ROW 
BEGIN
    INSERT INTO `transactions` (
        queue_number, 
        counter_id, 
        operator_id, 
        status, 
        time_created,
        notes
    ) 
    SELECT
        c.Counter_CurrentNumber,
        NEW.Counter_ID,
        NEW.User_ID,
        'Awaiting',
        NEW.Start_Time,
        'Auto-created from awaiting queue'
    FROM counters c 
    WHERE c.Counter_ID = NEW.Counter_ID
    ON DUPLICATE KEY UPDATE
        counter_id = NEW.Counter_ID,
        operator_id = NEW.User_ID,
        status = 'Awaiting',
        time_created = NEW.Start_Time,
        updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger: Sync when transaction is completed
CREATE TRIGGER `sync_complete_to_transactions` 
AFTER INSERT ON `complete` 
FOR EACH ROW 
BEGIN
    INSERT INTO `transactions` (
        queue_number, 
        counter_id, 
        status, 
        time_served, 
        duration,
        notes
    ) 
    VALUES (
        NEW.Complete_Number,
        NEW.Counter_ID,
        'Complete',
        NEW.End_Time,
        NEW.Duration,
        'Auto-completed from system'
    )
    ON DUPLICATE KEY UPDATE
        status = 'Complete',
        time_served = NEW.End_Time,
        duration = NEW.Duration,
        notes = CONCAT(COALESCE(notes, ''), ' | Completed: ', NOW()),
        updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- ===================================
-- 7. MIGRATE EXISTING DATA
-- ===================================

-- Migrate existing awaiting data
INSERT INTO `transactions` (queue_number, counter_id, operator_id, status, time_created, notes)
SELECT 
    c.Counter_CurrentNumber,
    a.Counter_ID,
    a.User_ID,
    'Awaiting',
    a.Start_Time,
    'Migrated from existing awaiting data'
FROM awaiting a
LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
WHERE c.Counter_CurrentNumber IS NOT NULL
ON DUPLICATE KEY UPDATE
    counter_id = VALUES(counter_id),
    operator_id = VALUES(operator_id),
    status = 'Awaiting',
    time_created = VALUES(time_created),
    updated_at = CURRENT_TIMESTAMP;

-- Migrate existing completed data
INSERT INTO `transactions` (queue_number, counter_id, status, time_served, duration, notes)
SELECT 
    comp.Complete_Number,
    comp.Counter_ID,
    'Complete',
    comp.End_Time,
    comp.Duration,
    'Migrated from existing completed data'
FROM complete comp
WHERE comp.Complete_Number IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = 'Complete',
    time_served = VALUES(time_served),
    duration = VALUES(duration),
    notes = CONCAT(COALESCE(notes, ''), ' | Migrated completed'),
    updated_at = CURRENT_TIMESTAMP;

-- ===================================
-- 8. CREATE ADDITIONAL INDEXES FOR PERFORMANCE
-- ===================================

-- Additional performance indexes
CREATE INDEX `idx_transactions_queue_status` ON `transactions` (`queue_number`, `status`);
CREATE INDEX `idx_transactions_created_status` ON `transactions` (`time_created`, `status`);
CREATE INDEX `idx_transactions_served_duration` ON `transactions` (`time_served`, `duration`);

-- Indexes for existing tables if not present
CREATE INDEX `idx_awaiting_counter_time` ON `awaiting` (`Counter_ID`, `Start_Time`);
CREATE INDEX `idx_complete_counter_time` ON `complete` (`Counter_ID`, `End_Time`);
CREATE INDEX `idx_counters_status` ON `counters` (`Counter_Status`);
CREATE INDEX `idx_users_active` ON `users` (`Status`);

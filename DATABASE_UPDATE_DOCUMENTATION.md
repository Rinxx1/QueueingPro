# Database Update Documentation

## Overview
This document explains the database updates made to support the improved transaction management system in QueueingPro.

## Changes Made

### 1. New Transactions Table
A centralized `transactions` table has been created to provide unified transaction management:

```sql
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
    KEY `time_created` (`time_created`)
);
```

### 2. Unified Transactions View
A view `v_all_transactions` combines data from both `awaiting` and `complete` tables:

- **Awaiting Transactions**: Shows current queue with operator assignments
- **Completed Transactions**: Shows historical data with service duration
- **Unified Format**: Consistent column structure for easy querying

### 3. Performance Indexes
- `idx_transactions_status_date`: For filtering by status and date
- `idx_transactions_counter_date`: For counter-specific reports

## Data Migration

The update script automatically migrates existing data:

1. **From `awaiting` table**: 
   - Maps to `Awaiting` status in transactions
   - Preserves counter and operator assignments
   - Uses `Start_Time` as `time_created`

2. **From `complete` table**:
   - Maps to `Complete` status in transactions
   - Preserves service duration and end time
   - Uses `End_Time` as `time_served`

## Benefits

### 1. **Unified Data Access**
- Single table for all transaction queries
- Consistent data structure across statuses
- Simplified reporting and analytics

### 2. **Better Performance**
- Optimized indexes for common queries
- Reduced JOIN operations
- Faster dashboard loading

### 3. **Enhanced Analytics**
- Easy calculation of completion rates
- Service time analysis
- Counter performance metrics
- Peak hours identification

### 4. **Data Integrity**
- Unique queue numbers
- Proper foreign key relationships
- Automatic timestamp management

## Usage in Transaction Management

### Frontend Integration
The transaction management interface now uses:
- `v_all_transactions` view for displaying data
- Filter capabilities by status, counter, and date
- Real-time statistics calculation

### Backend Functions
New functions support:
- Unified transaction listing
- Advanced filtering options
- Performance analytics
- Export capabilities

## Running the Update

### Method 1: Simple Update (Recommended)
```bash
cd /xampp/htdocs/QueueingPro
php simple_database_update.php
```

### Method 2: Manual SQL Execution
1. Import `database_updates.sql` via phpMyAdmin
2. Or execute statements manually in MySQL

### Method 3: Command Line
```bash
mysql -u root -p que_db < database_updates.sql
```

## Verification

After running the update, verify:

1. **Table Creation**:
   ```sql
   SHOW TABLES LIKE 'transactions';
   ```

2. **Data Migration**:
   ```sql
   SELECT status, COUNT(*) FROM transactions GROUP BY status;
   ```

3. **View Functionality**:
   ```sql
   SELECT COUNT(*) FROM v_all_transactions;
   ```

## Backup Recommendation

Before running updates:
1. Backup your `que_db` database
2. Test on a development environment first
3. Verify all existing functionality works

## Troubleshooting

### Common Issues:

1. **Foreign Key Errors**: Ensure `counters` and `users` tables exist
2. **Duplicate Key Errors**: Clear any existing conflicting data
3. **Permission Errors**: Run with appropriate database privileges

### Recovery:
If issues occur, restore from backup and contact support.

## Future Enhancements

The new structure supports:
- Real-time synchronization triggers
- Advanced reporting features
- Historical data analysis
- Performance optimization
- API integration capabilities

---

**Note**: This update maintains backward compatibility with existing `awaiting` and `complete` tables while adding enhanced functionality through the new `transactions` table and unified view.

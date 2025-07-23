# Queue Number Reset System - Documentation

## Overview
This system provides daily automatic queue number reset and manual reset functionality for the QueueingPro system.

## Features

### 1. Daily Automatic Reset
- **Trigger**: Automatically checks and performs reset on the first access each day
- **Action**: Resets all counter queue numbers to their base format (A000, B000, etc.)
- **Logging**: Records reset actions in the `system_logs` table
- **Prevention**: Prevents duplicate resets on the same day

### 2. Manual Reset Button
- **Location**: Counter Management page (admin/counters.php)
- **Access**: Admin users only
- **Confirmation**: Requires user confirmation before executing
- **Action**: Immediately resets all queue numbers and clears awaiting queues

### 3. New Counter Creation
- **Starting Number**: New counters now start with A000 instead of A001
- **Format**: Follows alphabetical pattern (A000, B000, C000, etc.)
- **Auto-generation**: Automatically finds the next available letter

## Technical Implementation

### Database Changes
- **system_logs table**: Automatically created if it doesn't exist
  - `log_id` (Primary Key)
  - `log_date` (Timestamp)
  - `log_action` (VARCHAR)
  - `log_details` (TEXT)
  - `created_at` (Timestamp)

### Functions Added
1. `createSystemLogsTable()` - Creates the logging table
2. `resetDailyQueueNumbers()` - Performs daily reset
3. `checkAndPerformDailyReset()` - Checks and performs reset if needed
4. `manualResetQueueNumbers()` - Manual reset function
5. Modified `getNextCounterName()` - Returns A000 format instead of A001

### AJAX Endpoints
- `reset_queue_numbers` - Manual reset action
- `check_daily_reset` - Daily reset check action

### UI Changes
- Added "Reset Queue Numbers" button to counters management
- Updated counter creation form to show A000 format
- Enhanced user feedback with SweetAlert confirmations

## Usage Instructions

### For Admins:
1. **Automatic Reset**: No action needed - happens automatically each day
2. **Manual Reset**: 
   - Go to Counters Management page
   - Click "Reset Queue Numbers" button
   - Confirm the action in the popup
3. **Monitor Resets**: Check system logs for reset history

### For System Behavior:
- All counters will reset to X000 format (where X is the counter letter)
- Awaiting queues are cleared during reset
- Only one reset per day is allowed (automatic)
- Manual resets can be performed at any time

## Error Handling
- Database errors are logged and user-friendly messages displayed
- Table creation is automatic if missing
- Graceful handling of connection issues
- Prevents duplicate daily resets

## Logging
All reset actions are logged with:
- Timestamp
- Action type (DAILY_RESET or MANUAL_RESET)
- Details of the operation
- Success/failure status

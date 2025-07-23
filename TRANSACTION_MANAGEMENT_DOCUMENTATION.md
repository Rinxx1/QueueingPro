# Transaction Management System Documentation

## Overview
The Transaction Management System provides comprehensive monitoring and management capabilities for all queue transactions within the QueueingPro system. It includes real-time analytics, filtering, reporting, and transaction lifecycle management.

## Database Schema

### Transactions Table Structure
```sql
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    queue_number VARCHAR(10) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    counter_id INT NOT NULL,
    operator_id INT,
    customer_name VARCHAR(255),
    customer_contact VARCHAR(100),
    priority_level ENUM('Low', 'Normal', 'High', 'VIP') DEFAULT 'Normal',
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Waiting',
    service_rating TINYINT(1) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Key Features

#### 1. Real-time Statistics
- **Total Transactions Today**: Count of all transactions created today
- **Completed Today**: Count of completed transactions today
- **Currently Processing**: Count of transactions with "In Progress" status
- **Average Wait Time**: Average duration of completed transactions in minutes

#### 2. Advanced Filtering
- **Search**: Full-text search across queue numbers, customer names, and service types
- **Service Type**: Filter by specific service categories
- **Status**: Filter by transaction status (Waiting, In Progress, Completed, Cancelled, No Show)
- **Counter**: Filter by specific counters
- **Date Range**: Filter by today, this week, or this month

#### 3. Transaction Lifecycle Management
- **Create**: Add new transactions manually or automatically
- **Start**: Begin processing a waiting transaction
- **Complete**: Mark transaction as completed with optional rating and notes
- **Cancel**: Cancel transactions with reason tracking
- **View**: Detailed transaction information display

#### 4. Analytics and Reporting
- **Service Type Distribution**: Visual breakdown of transaction types
- **Peak Hours Analysis**: Identification of busy periods
- **Quality Metrics**: Customer satisfaction ratings and completion rates
- **Export Functionality**: CSV export of filtered transaction data

## Service Types
The system supports the following predefined service types:
- General Inquiry
- Account Services
- Loan Services
- Card Services
- Deposits
- Withdrawals
- Money Transfer
- Investment Services
- Insurance Services
- Bill Payment
- Check Encashment
- Foreign Exchange
- Customer Complaints
- Document Processing
- Others

## Priority Levels
- **Low**: Non-urgent transactions
- **Normal**: Standard priority (default)
- **High**: Urgent transactions requiring faster processing
- **VIP**: Priority customers with expedited service

## Transaction Status Flow
1. **Waiting**: Initial state when transaction is created
2. **In Progress**: When operator starts serving the customer
3. **Completed**: Successfully finished transaction
4. **Cancelled**: Transaction cancelled by operator or system
5. **No Show**: Customer didn't appear for service

## API Endpoints

### GET Endpoints
- `get_transactions`: Retrieve filtered transaction list
- `get_stats`: Get transaction statistics
- `get_service_analytics`: Service type distribution data
- `get_hourly_analytics`: Hourly transaction patterns
- `get_service_types`: Available service types
- `get_counters`: Available counters
- `get_operators`: Available operators

### POST Endpoints
- `create_transaction`: Create new transaction
- `update_transaction`: Update existing transaction
- `start_transaction`: Start transaction processing
- `complete_transaction`: Complete transaction with rating
- `cancel_transaction`: Cancel transaction with reason
- `delete_transaction`: Delete transaction record
- `generate_sample_data`: Create sample transactions for testing

## File Structure
```
admin/transactions/
├── functions.php     # Core business logic and database operations
├── ajax.php         # AJAX endpoint handlers
├── scripts.js       # Frontend JavaScript functionality
└── transactions.css # Styling for transaction management
```

## Security Features
- **Input Validation**: All inputs are validated and sanitized
- **SQL Injection Prevention**: Using prepared statements
- **XSS Protection**: Output encoding for all dynamic content
- **Access Control**: Restricted to authenticated admin users

## Performance Optimizations
- **Database Indexing**: Optimized indexes on frequently queried columns
- **AJAX Loading**: Asynchronous data loading for better user experience
- **Auto-refresh**: Periodic data updates every 30 seconds
- **Debounced Search**: Optimized search with input debouncing

## Usage Instructions

### Adding a New Transaction
1. Click "Add Transaction" button
2. Fill in required fields (Queue Number, Service Type, Counter)
3. Optionally add customer information and notes
4. Submit to create the transaction

### Managing Transaction Flow
1. **Start**: Click play button on waiting transactions
2. **Complete**: Click check button on in-progress transactions
3. **Cancel**: Click X button with reason selection
4. **View**: Click eye button for detailed information

### Filtering and Search
1. Use the search box for text-based queries
2. Select specific filters from dropdown menus
3. Choose date range for time-based filtering
4. Results update automatically

### Exporting Data
1. Apply desired filters
2. Click "Export Report" button
3. CSV file will be downloaded with filtered results

## Integration Points
- **Counters System**: Links to counter management for operator assignments
- **Users System**: Links to user management for operator information
- **Queue Display**: Real-time updates to display system

## Sample Data Generation
The system includes a sample data generator for testing purposes:
- Creates 50 sample transactions for the current day
- Includes realistic service types, durations, and statuses
- Provides test data for analytics validation

## Maintenance
- **Database Cleanup**: Regular cleanup of old transaction records
- **Performance Monitoring**: Monitor query performance and optimize as needed
- **Data Backup**: Regular backup of transaction data
- **Analytics Review**: Periodic review of analytics accuracy

## Future Enhancements
- **Advanced Reporting**: More detailed reports and charts
- **Email Notifications**: Automated notifications for specific events
- **Customer Feedback**: Direct customer rating collection
- **Mobile Integration**: Mobile app support for transaction management
- **API Integration**: External system integration capabilities

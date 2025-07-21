-- Add Counter_Description column to counters table if it doesn't exist
-- Run this SQL script in your database

ALTER TABLE counters 
ADD COLUMN Counter_Description TEXT DEFAULT NULL 
AFTER Counter_Name;

-- Update existing counters with a default description
UPDATE counters 
SET Counter_Description = 'General service counter' 
WHERE Counter_Description IS NULL;
